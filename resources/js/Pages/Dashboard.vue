<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { auth } = usePage().props;
const photoPreview = ref(null);
const videoPreview = ref(null);
const fileInput = ref(null);
const isRecording = ref(false);
const recordingTime = ref(0);
const showVideoStream = ref(false);
const videoStreamElement = ref(null);

let mediaRecorder = null;
let recordedChunks = [];
let stream = null;
let recordingInterval = null;

const openCamera = () => {
    fileInput.value.click();
};

const startVideoRecording = async () => {
    try {
        // Richiedi accesso alla fotocamera
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
            audio: true
        });

        // Mostra il feed video
        showVideoStream.value = true;
        await new Promise(resolve => setTimeout(resolve, 100)); // Attendi il render
        if (videoStreamElement.value) {
            videoStreamElement.value.srcObject = stream;
        }

        // Crea MediaRecorder
        mediaRecorder = new MediaRecorder(stream);
        recordedChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                recordedChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            videoPreview.value = URL.createObjectURL(blob);

            // Ferma lo stream
            stream.getTracks().forEach(track => track.stop());
            isRecording.value = false;
            showVideoStream.value = false;
            recordingTime.value = 0;
            clearInterval(recordingInterval);
        };

        // Avvia la registrazione
        mediaRecorder.start();
        isRecording.value = true;
        recordingTime.value = 0;

        // Timer per il countdown
        recordingInterval = setInterval(() => {
            recordingTime.value++;
        }, 1000);

        // Ferma automaticamente dopo 5 secondi
        setTimeout(() => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                stopVideoRecording();
            }
        }, 5000);

    } catch (error) {
        console.error('Errore nell\'accesso alla fotocamera:', error);
        alert('Impossibile accedere alla fotocamera. Verifica i permessi del browser.');
        isRecording.value = false;
        showVideoStream.value = false;
    }
};

const stopVideoRecording = () => {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
};

const handlePhotoCapture = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- ADMIN-->
                <div v-if="auth.user.is_admin" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold">Admin</h2>
                    </div>
                </div>

                <!-- USER -->
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold">Utente</h2>

                        <div v-if="photoPreview" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Anteprima Foto:</h3>
                            <img
                                :src="photoPreview"
                                alt="Foto scattata"
                                class="max-w-full h-auto rounded-lg shadow-lg"
                            />
                        </div>

                        <div v-if="showVideoStream" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">
                                Registrazione in corso... {{ 5 - recordingTime }}s
                            </h3>
                            <video
                                ref="videoStreamElement"
                                autoplay
                                playsinline
                                muted
                                class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
                            />
                            <button
                                @click="stopVideoRecording"
                                class="mt-2 px-4 w-full py-2 bg-red-600 text-white rounded-md"
                            >
                                Ferma Registrazione
                            </button>
                        </div>

                        <div v-if="videoPreview && !showVideoStream" class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Anteprima Video:</h3>
                            <video
                                :src="videoPreview"
                                controls
                                class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
                            />
                        </div>

                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            capture="environment"
                            @change="handlePhotoCapture"
                            class="hidden"
                        />

                        <button
                            @click="openCamera"
                            class="mt-4 px-4 w-full py-2 text-black rounded-md bg-gradient-to-r from-amber-200 to-yellow-500 hover:from-amber-300 hover:to-yellow-600 transition-colors"
                        >
                            📷 Scatta Foto
                        </button>

                        <button
                            @click="startVideoRecording"
                            :disabled="isRecording"
                            class="mt-4 px-4 w-full py-2 text-black rounded-md bg-gradient-to-r from-green-200 to-green-500 hover:from-green-300 hover:to-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            🎥 Registra Video (5s)
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
