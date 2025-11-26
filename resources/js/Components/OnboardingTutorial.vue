<script setup>
import { ref, computed } from 'vue';

const emit = defineEmits(['complete']);

const currentStep = ref(0);

const steps = [
    {
        title: 'Benvenuto!',
        description: 'Questo è il portale per condividere i tuoi momenti speciali con noi.',
        icon: '👋'
    },
    {
        title: 'Foto & Video',
        description: 'Puoi scattare una foto o registrare un video di massimo 5 secondi. I contenuti saranno visibili solo agli amministratori.',
        icon: '📸'
    },
    {
        title: 'Pronto?',
        description: 'Clicca sui pulsanti per iniziare a catturare i tuoi ricordi. Divertiti!',
        icon: '🎉'
    }
];

const isLastStep = computed(() => currentStep.value === steps.length - 1);
const isFirstStep = computed(() => currentStep.value === 0);

const nextStep = () => {
    if (isLastStep.value) {
        completeTutorial();
    } else {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (!isFirstStep.value) {
        currentStep.value--;
    }
};

const completeTutorial = () => {
    localStorage.setItem('tutorialCompleted', 'true');
    emit('complete');
};

const skipTutorial = () => {
    completeTutorial();
};
</script>

<template>
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl">
            <!-- Progress indicators -->
            <div class="flex justify-center gap-2 pt-6 px-6">
                <div
                    v-for="(step, index) in steps"
                    :key="index"
                    class="h-1.5 rounded-full transition-all duration-300"
                    :class="[
                        index <= currentStep ? 'bg-blue-600' : 'bg-gray-200',
                        index === currentStep ? 'w-8' : 'w-4'
                    ]"
                />
            </div>

            <!-- Step content with animation -->
            <div class="relative h-72 overflow-hidden">
                <TransitionGroup name="slide">
                    <div
                        v-for="(step, index) in steps"
                        :key="index"
                        v-show="currentStep === index"
                        class="absolute inset-0 flex flex-col items-center justify-center p-8 text-center"
                    >
                        <span class="text-6xl mb-6 animate-bounce-slow">{{ step.icon }}</span>
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ step.title }}</h2>
                        <p class="text-gray-600 leading-relaxed">{{ step.description }}</p>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Navigation buttons -->
            <div class="p-6 bg-gray-50 flex justify-between items-center">
                <button
                    v-if="!isFirstStep"
                    @click="prevStep"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
                >
                    Indietro
                </button>
                <button
                    v-else
                    @click="skipTutorial"
                    class="px-4 py-2 text-gray-400 hover:text-gray-600 transition-colors text-sm"
                >
                    Salta
                </button>

                <button
                    @click="nextStep"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                    {{ isLastStep ? 'Inizia' : 'Avanti' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.4s ease-out;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(50px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-50px);
}

.animate-bounce-slow {
    animation: bounce-slow 2s infinite;
}

@keyframes bounce-slow {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>
