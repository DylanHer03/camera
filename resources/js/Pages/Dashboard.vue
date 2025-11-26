<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OnboardingTutorial from '@/Components/OnboardingTutorial.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import { Camera, Clapperboard } from 'lucide-vue-next';

const { auth } = usePage().props;

const currentView = ref('buttons');
const showTutorial = ref(false);

const cameraStreamElement = ref(null);
const videoStreamElement = ref(null);
const canvasElement = ref(null);

const photoData = ref(null);
const videoData = ref(null);
const recordingTime = ref(0);
const recordingActive = ref(false);

let cameraStream = null;
let videoStream = null;
let mediaRecorder = null;
let recordedChunks = [];
let recordingInterval = null;

onMounted(() => {
    const tutorialCompleted = localStorage.getItem('tutorialCompleted');
    if (!tutorialCompleted && !auth.user.is_admin) {
        showTutorial.value = true;
    }
});

const onTutorialComplete = () => {
    showTutorial.value = false;
};

watch(cameraStreamElement, (el) => {
    if (el && cameraStream) {
        el.srcObject = cameraStream;
    }
});

watch(videoStreamElement, (el) => {
    if (el && videoStream) {
        el.srcObject = videoStream;
    }
});

// === FOTO ===
const openCamera = async () => {
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        });
        currentView.value = 'camera';
    } catch (error) {
        console.error('Errore accesso fotocamera:', error);
        alert('Impossibile accedere alla fotocamera. Verifica i permessi.');
    }
};

const capturePhoto = () => {
    if (!cameraStreamElement.value || !canvasElement.value) return;

    const video = cameraStreamElement.value;
    const canvas = canvasElement.value;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);

    photoData.value = canvas.toDataURL('image/jpeg', 0.9);
    stopCameraStream();
    currentView.value = 'photo-preview';
};

const stopCameraStream = () => {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
};

const cancelCamera = () => {
    stopCameraStream();
    currentView.value = 'buttons';
};

// === VIDEO ===
const openVideoRecorder = async () => {
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: true
        });
        currentView.value = 'video';
    } catch (error) {
        console.error('Errore accesso fotocamera:', error);
        alert('Impossibile accedere alla fotocamera. Verifica i permessi.');
    }
};

const startRecording = () => {
    if (!videoStream) return;

    // Safari supporta mp4, Chrome supporta webm
    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9')
        ? 'video/webm;codecs=vp9'
        : MediaRecorder.isTypeSupported('video/webm')
            ? 'video/webm'
            : 'video/mp4';

    const options = { mimeType };

    try {
        mediaRecorder = new MediaRecorder(videoStream, options);
    } catch (e) {
        mediaRecorder = new MediaRecorder(videoStream);
    }

    recordedChunks = [];

    mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
            recordedChunks.push(event.data);
        }
    };

    mediaRecorder.onstop = () => {
        const blob = new Blob(recordedChunks, { type: mediaRecorder.mimeType });
        videoData.value = URL.createObjectURL(blob);
        stopVideoStream();
        currentView.value = 'video-preview';
    };

    mediaRecorder.start();
    recordingActive.value = true;
    recordingTime.value = 0;

    recordingInterval = setInterval(() => {
        recordingTime.value++;
        if (recordingTime.value >= 5) {
            stopRecording();
        }
    }, 1000);
};

const stopRecording = () => {
    clearInterval(recordingInterval);
    recordingActive.value = false;
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
};

const stopVideoStream = () => {
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    recordingTime.value = 0;
    recordingActive.value = false;
};

const cancelVideo = () => {
    stopRecording();
    stopVideoStream();
    currentView.value = 'buttons';
};

// === NAVIGATION ===
const backToButtons = () => {
    currentView.value = 'buttons';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <OnboardingTutorial
            v-if="showTutorial"
            @complete="onTutorialComplete"
        />

        <div class="h-full pt-6 px-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="auth.user.is_admin" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold">Admin</h2>
                    </div>
                </div>

                <div v-else class="p-2 px-1">
                    <Transition name="slide" mode="out-in">
                        <!-- Pulsanti scelta -->
                        <div v-if="currentView === 'buttons'" key="buttons" class="space-y-6">
                            <div class="flex flex-col gap-4">
                                <button
                                    @click="openCamera"
                                    class="flex items-center justify-center gap-2 p-4 bg-bgd rounded-xl hover:shadow-lg transition-all duration-200 hover:scale-105 active:scale-95"
                                >
                                    <Camera fill="white" />
                                    <span class="font-semibold text-white">Foto</span>
                                </button>

                                <button
                                    @click="openVideoRecorder"
                                    class="flex items-center justify-center gap-2 p-4 bg-bgd rounded-xl shadow-md hover:shadow-lg transition-all duration-200 hover:scale-105 active:scale-95"
                                >
                                    <Clapperboard fill="white" />
                                    <span class="font-semibold text-white">Video</span>
                                    <span class="dot"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Camera per foto -->
                        <div v-else-if="currentView === 'camera'" key="camera" class="space-y-4">
                            <h3 class="text-lg font-semibold text-center">Inquadra e scatta</h3>
                            <video
                                ref="cameraStreamElement"
                                autoplay
                                playsinline
                                muted
                                class="w-full rounded-xl shadow-lg bg-black h-[65vh]"
                            ></video>
                            <canvas ref="canvasElement" class="hidden"></canvas>
                            <div class="flex gap-3">
                                <button
                                    @click="cancelCamera"
                                    class="flex-1 py-3 bg-gray-200 text-black rounded-lg font-medium"
                                >
                                    Annulla
                                </button>
                                <button
                                    @click="capturePhoto"
                                    class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-semibold"
                                >
                                    Scatta
                                </button>
                            </div>
                        </div>

                        <!-- Video recorder -->
                        <div v-else-if="currentView === 'video'" key="video" class="space-y-4">
                            <h3 class="text-lg font-semibold text-center">
                                <span v-if="recordingActive">Registrazione {{ 5 - recordingTime }}s</span>
                                <span v-else>Premi per registrare</span>
                            </h3>
                            <video
                                ref="videoStreamElement"
                                autoplay
                                playsinline
                                muted
                                class="w-full rounded-xl shadow-lg bg-black"
                            ></video>
                            <div class="flex gap-3">
                                <button
                                    @click="cancelVideo"
                                    class="flex-1 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium"
                                >
                                    Annulla
                                </button>
                                <button
                                    v-if="!recordingActive"
                                    @click="startRecording"
                                    class="flex-1 py-3 bg-red-600 text-white rounded-lg font-semibold"
                                >
                                    Registra
                                </button>
                                <button
                                    v-else
                                    @click="stopRecording"
                                    class="flex-1 py-3 bg-red-800 text-white rounded-lg font-semibold animate-pulse"
                                >
                                    Ferma
                                </button>
                            </div>
                        </div>

                        <!-- Preview foto -->
                        <div v-else-if="currentView === 'photo-preview'" key="photo-preview" class="space-y-4">
                            <h3 class="text-lg font-semibold text-center">La tua foto</h3>
                            <img
                                :src="photoData"
                                alt="Foto scattata"
                                class="w-full rounded-xl shadow-lg h-[65vh] object-cover"
                            />
                            <div class="flex gap-3">
                                <button
                                    @click="backToButtons"
                                    class="flex-1 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium"
                                >
                                    Indietro
                                </button>
                                <button class="flex-1 py-3 bg-green-600 text-white rounded-lg font-semibold">
                                    Invia
                                </button>
                            </div>
                        </div>

                        <!-- Preview video -->
                        <div v-else-if="currentView === 'video-preview'" key="video-preview" class="space-y-4">
                            <h3 class="text-lg font-semibold text-center">Il tuo video</h3>
                            <video
                                :src="videoData"
                                controls
                                playsinline
                                class="w-full rounded-xl shadow-lg"
                            ></video>
                            <div class="flex gap-3">
                                <button
                                    @click="backToButtons"
                                    class="flex-1 py-3 bg-gray-200 text-gray-800 rounded-lg font-medium"
                                >
                                    Indietro
                                </button>
                                <button class="flex-1 py-3 bg-green-600 text-white rounded-lg font-semibold">
                                    Invia
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.slide-enter-active {
    transition: all 0.3s ease-out;
}
.slide-leave-active {
    transition: all 0.2s ease-in;
}
.slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>
