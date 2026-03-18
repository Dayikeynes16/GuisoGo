<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Seleccionar' },
    disabled: { type: Boolean, default: false },
    hasError: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const listRef = ref(null)
const triggerRef = ref(null)

// Generate 48 time slots (00:00 to 23:30 in 30-min intervals).
const timeSlots = computed(() => {
    const slots = []
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 30) {
            const hh = String(h).padStart(2, '0')
            const mm = String(m).padStart(2, '0')
            slots.push(`${hh}:${mm}`)
        }
    }
    return slots
})

const displayValue = computed(() => {
    if (!props.modelValue) { return null }
    const [h, m] = props.modelValue.split(':')
    const hour = parseInt(h, 10)
    const suffix = hour >= 12 ? 'p.m.' : 'a.m.'
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour
    return `${displayHour}:${m} ${suffix}`
})

function toggle() {
    if (props.disabled) { return }
    open.value = !open.value
    if (open.value) {
        nextTick(() => scrollToSelected())
    }
}

function select(time) {
    emit('update:modelValue', time)
    open.value = false
}

function scrollToSelected() {
    if (!listRef.value || !props.modelValue) { return }
    const index = timeSlots.value.indexOf(props.modelValue)
    if (index === -1) { return }
    const el = listRef.value.children[index]
    if (el) {
        el.scrollIntoView({ block: 'center' })
    }
}

function formatSlotLabel(time) {
    const [h, m] = time.split(':')
    const hour = parseInt(h, 10)
    const suffix = hour >= 12 ? 'p.m.' : 'a.m.'
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour
    return `${displayHour}:${m} ${suffix}`
}

// Close on outside click.
function onClickOutside(e) {
    if (triggerRef.value && !triggerRef.value.contains(e.target)) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside))
</script>

<template>
    <div ref="triggerRef" class="relative">
        <!-- Trigger button -->
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="w-full flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-sm transition-colors"
            :class="[
                hasError ? 'border-red-400' : 'border-gray-200 hover:border-gray-300',
                open ? 'ring-2 ring-[#FF5722]/30 border-[#FF5722]' : '',
                disabled ? 'opacity-50 cursor-not-allowed bg-gray-50' : 'bg-white cursor-pointer',
            ]"
        >
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-400 text-lg">schedule</span>
                <span :class="displayValue ? 'text-gray-900 font-medium' : 'text-gray-400'">
                    {{ displayValue || placeholder }}
                </span>
            </div>
            <span
                class="material-symbols-outlined text-gray-400 text-lg transition-transform"
                :class="{ 'rotate-180': open }"
            >expand_more</span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden"
            >
                <div
                    ref="listRef"
                    class="max-h-52 overflow-y-auto py-1 scroll-smooth"
                >
                    <button
                        v-for="time in timeSlots"
                        :key="time"
                        type="button"
                        @click="select(time)"
                        class="w-full px-4 py-2 text-sm text-left transition-colors"
                        :class="time === modelValue
                            ? 'bg-[#FF5722] text-white font-semibold'
                            : 'text-gray-700 hover:bg-orange-50 hover:text-[#FF5722]'"
                    >
                        {{ formatSlotLabel(time) }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
