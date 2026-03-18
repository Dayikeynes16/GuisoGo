<script setup>
defineProps({
    modelValue: { type: Boolean, required: true },
    disabled: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

function toggle() {
    emit('update:modelValue', !arguments[0])
}
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        :disabled="disabled"
        @click="!disabled && emit('update:modelValue', !modelValue)"
        class="w-10 h-6 rounded-full transition-colors relative shrink-0"
        :class="[
            modelValue ? 'bg-[#FF5722]' : 'bg-gray-200',
            disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer',
        ]"
    >
        <div
            class="absolute top-1 w-4 h-4 rounded-full bg-white shadow transition-all"
            :class="modelValue ? 'left-5' : 'left-1'"
        />
    </button>
</template>
