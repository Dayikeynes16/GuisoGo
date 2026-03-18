<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue'

const props = defineProps({
    orders_by_day: Object,
    top_restaurants: Array,
})

const chartEntries = computed(() =>
    Object.entries(props.orders_by_day || {}).map(([date, count]) => [date, parseInt(count, 10) || 0]),
)
const maxCount = computed(() => Math.max(1, ...chartEntries.value.map(([, c]) => c)))

const yTicks = computed(() => {
    const m = maxCount.value
    if (m <= 5) { return Array.from({ length: m + 1 }, (_, i) => i) }
    const step = Math.ceil(m / 4)
    const ticks = []
    for (let i = 0; i <= m; i += step) { ticks.push(i) }
    if (ticks[ticks.length - 1] < m) { ticks.push(m) }
    return ticks
})

function barHeight(count) {
    return Math.max(3, (count / maxCount.value) * 100)
}

function formatDate(dateStr) {
    const [, m, d] = dateStr.split('-')
    return `${d}/${m}`
}

function showLabel(index) {
    const total = chartEntries.value.length
    if (total <= 15) { return true }
    if (total <= 20) { return index % 2 === 0 }
    return index % 3 === 0
}

const totalOrders = computed(() => chartEntries.value.reduce((s, [, c]) => s + c, 0))
const avgDaily = computed(() => {
    const days = chartEntries.value.length
    if (days === 0) { return '0' }
    return (totalOrders.value / days).toFixed(1)
})
</script>

<template>
    <Head title="SuperAdmin — Estadísticas" />
    <SuperAdminLayout>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Estadísticas</h1>
            <p class="mt-1 text-sm text-gray-500">Vista global de pedidos en los últimos 30 días.</p>
        </div>

        <!-- Orders by day -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 mb-6">Pedidos por día (últimos 30 días)</h2>

            <div v-if="chartEntries.length === 0" class="text-center py-12 text-sm text-gray-400">
                Sin datos disponibles.
            </div>
            <div v-else class="relative">
                <!-- Chart area -->
                <div class="flex">
                    <!-- Y axis -->
                    <div class="flex flex-col justify-between h-56 pr-3 shrink-0">
                        <span v-for="tick in [...yTicks].reverse()" :key="tick" class="text-xs text-gray-400 text-right tabular-nums leading-none">
                            {{ tick }}
                        </span>
                    </div>

                    <!-- Bars area -->
                    <div class="flex-1 min-w-0 relative">
                        <!-- Horizontal grid lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                            <div v-for="tick in yTicks" :key="'g'+tick" class="border-t border-gray-100 w-full" :class="{ 'border-gray-200': tick === 0 }"></div>
                        </div>

                        <!-- Bars -->
                        <div class="relative flex items-end gap-[3px] h-56 overflow-x-auto no-scrollbar">
                            <div
                                v-for="([date, count], index) in chartEntries"
                                :key="date"
                                class="flex-1 min-w-[14px] max-w-[28px] flex flex-col items-center group relative"
                                style="height: 100%;"
                            >
                                <!-- Tooltip -->
                                <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                                    {{ formatDate(date) }}: <strong>{{ count }}</strong> pedidos
                                </div>

                                <!-- Bar wrapper (pushes bar to bottom) -->
                                <div class="flex-1"></div>
                                <div
                                    class="w-full rounded-t-sm bg-[#FF5722]/70 group-hover:bg-[#FF5722] transition-colors cursor-default"
                                    :style="{ height: barHeight(count) + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- X axis labels -->
                        <div class="flex gap-[3px] mt-2">
                            <div
                                v-for="([date], index) in chartEntries"
                                :key="'l'+date"
                                class="flex-1 min-w-[14px] max-w-[28px] text-center"
                            >
                                <span v-if="showLabel(index)" class="text-[10px] text-gray-400 tabular-nums">
                                    {{ formatDate(date) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="flex items-center gap-8 mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Total pedidos</p>
                        <p class="text-xl font-bold text-gray-900">{{ totalOrders }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Promedio diario</p>
                        <p class="text-xl font-bold text-gray-900">{{ avgDaily }} <span class="text-xs font-normal text-gray-400">pedidos/dia</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Dia con mas pedidos</p>
                        <p class="text-xl font-bold text-gray-900">{{ maxCount }} <span class="text-xs font-normal text-gray-400">pedidos</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top restaurants -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Top restaurantes del mes</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <div v-if="top_restaurants.length === 0" class="px-6 py-8 text-center text-sm text-gray-400">
                    Sin datos disponibles.
                </div>
                <div
                    v-for="(restaurant, index) in top_restaurants"
                    :key="restaurant.id"
                    class="px-6 py-4 flex items-center gap-4"
                >
                    <span class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600 shrink-0">
                        {{ index + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ restaurant.name }}</p>
                        <p class="text-xs text-gray-400">{{ restaurant.slug }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900">{{ restaurant.monthly_orders_count }}</p>
                        <p class="text-xs text-gray-400">pedidos</p>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="restaurant.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    >
                        {{ restaurant.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
