<script setup>
import { computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from '@/Components/SettingsLayout.vue'
import TimePicker from '@/Components/TimePicker.vue'
import ToggleSwitch from '@/Components/ToggleSwitch.vue'

const props = defineProps({
    schedules: Array,
})

const DAY_ORDER = [1, 2, 3, 4, 5, 6, 0]
const DAY_NAMES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']

const sorted = DAY_ORDER.map((d) => props.schedules.find((s) => s.day_of_week === d) ?? {
    day_of_week: d, opens_at: '09:00', closes_at: '21:00', is_closed: true,
})

const form = useForm({
    schedules: sorted.map((s) => ({
        day_of_week: s.day_of_week,
        opens_at: s.opens_at ?? '09:00',
        closes_at: s.closes_at ?? '21:00',
        is_closed: s.is_closed ?? true,
    })),
})

const allClosed = computed(() => form.schedules.every((s) => s.is_closed))

function submit() {
    form.put(route('settings.schedules.update'))
}
</script>

<template>
    <Head title="Horarios" />
    <AppLayout title="Configuración">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona la información y preferencias de tu restaurante.</p>
        </div>

        <SettingsLayout>
            <div class="pb-20">
            <div v-if="allClosed" class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-4">
                <span class="material-symbols-outlined text-amber-500 text-xl shrink-0 mt-0.5">warning</span>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Tu restaurante aparece como cerrado</p>
                    <p class="text-sm text-amber-700 mt-0.5">No tienes ningún día con horario activo. Tus clientes verán que estás cerrado y no podrán realizar pedidos.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Horarios de atención</h2>
                    <p class="text-sm text-gray-500 mt-1">Define los días y horas que tu restaurante está abierto. Fuera de estos horarios, tus clientes verán que estás cerrado.</p>
                </div>

                <!-- Schedule rows -->
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="(schedule, index) in form.schedules"
                        :key="schedule.day_of_week"
                        class="px-6 py-4 flex items-center gap-4"
                    >
                        <!-- Day name -->
                        <span class="w-28 text-sm font-semibold text-gray-700 shrink-0">
                            {{ DAY_NAMES[schedule.day_of_week] }}
                        </span>

                        <!-- Toggle -->
                        <ToggleSwitch
                            :model-value="!schedule.is_closed"
                            @update:model-value="schedule.is_closed = !$event"
                        />

                        <span
                            class="text-xs font-medium w-16 shrink-0"
                            :class="!schedule.is_closed ? 'text-green-600' : 'text-gray-400'"
                        >
                            {{ !schedule.is_closed ? 'Abierto' : 'Cerrado' }}
                        </span>

                        <!-- Time inputs -->
                        <template v-if="!schedule.is_closed">
                            <div class="w-40">
                                <TimePicker v-model="schedule.opens_at" placeholder="Apertura" />
                            </div>
                            <span class="text-gray-400 text-sm">a</span>
                            <div class="w-40">
                                <TimePicker v-model="schedule.closes_at" placeholder="Cierre" />
                            </div>
                        </template>
                        <template v-else>
                            <span class="text-sm text-gray-400 italic">Sin horario</span>
                        </template>
                    </div>
                </div>

                <!-- Errors -->
                <div v-if="form.hasErrors" class="px-6 py-3 bg-red-50 border-t border-red-100">
                    <p class="text-sm text-red-600">Revisa los horarios ingresados. El formato debe ser HH:MM.</p>
                </div>
            </div>

            <!-- Action bar -->
            <div class="fixed bottom-0 left-[260px] right-0 bg-white border-t border-gray-200 px-8 py-4 flex justify-end z-20">
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="bg-[#FF5722] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#D84315] disabled:opacity-50 transition-colors"
                >
                    {{ form.processing ? 'Guardando...' : 'Guardar horarios' }}
                </button>
            </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
