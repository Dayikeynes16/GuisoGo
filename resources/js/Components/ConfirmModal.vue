<script setup>
const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: '¿Estás seguro?',
    },
    message: {
        type: String,
        default: 'Esta acción no se puede deshacer.',
    },
    confirmLabel: {
        type: String,
        default: 'Eliminar',
    },
    cancelLabel: {
        type: String,
        default: 'Cancelar',
    },
    destructive: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['confirm', 'cancel'])
</script>

<template>
    <Transition name="modal">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="emit('cancel')" />

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                            :class="destructive ? 'bg-red-50' : 'bg-orange-50'"
                        >
                            <span
                                class="material-symbols-outlined text-xl"
                                :class="destructive ? 'text-red-500' : 'text-[#FF5722]'"
                            >warning</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
                    </div>

                    <p class="text-sm text-gray-500 ml-[52px] mb-6">{{ message }}</p>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="emit('cancel')"
                            class="flex-1 border border-gray-200 text-gray-700 font-semibold rounded-xl py-2.5 text-sm hover:bg-gray-50 transition-colors"
                        >
                            {{ cancelLabel }}
                        </button>
                        <button
                            type="button"
                            @click="emit('confirm')"
                            class="flex-1 font-semibold rounded-xl py-2.5 text-sm transition-colors"
                            :class="destructive
                                ? 'bg-red-500 hover:bg-red-600 text-white'
                                : 'bg-[#FF5722] hover:bg-[#D84315] text-white'"
                        >
                            {{ confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.2s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}
</style>
