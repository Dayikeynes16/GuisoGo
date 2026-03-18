<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from '@/Components/SettingsLayout.vue'

const props = defineProps({
    operator: Object,
    branches: Array,
})

const form = useForm({
    name: props.operator.name,
    email: props.operator.email,
    password: '',
    branch_ids: props.operator.branches?.map((b) => b.id) ?? [],
})

function toggleBranch(id) {
    const idx = form.branch_ids.indexOf(id)
    if (idx === -1) { form.branch_ids.push(id) }
    else { form.branch_ids.splice(idx, 1) }
}

function submit() {
    form.put(route('settings.users.update', props.operator.id))
}
</script>

<template>
    <Head title="Editar Usuario" />
    <AppLayout title="Configuracion">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuracion</h1>
        </div>

        <SettingsLayout>
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">Editar usuario operador</h2>
            </div>

            <form @submit.prevent="submit" class="space-y-5 max-w-lg">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722]"
                        :class="{ 'border-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electronico</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722]"
                        :class="{ 'border-red-400': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contrasena</label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Dejar vacio para no cambiar"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722]"
                        :class="{ 'border-red-400': form.errors.password }"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    <p v-else class="mt-1 text-xs text-gray-400">Dejar vacio para mantener la actual.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sucursales asignadas</label>
                    <p v-if="form.errors.branch_ids" class="text-xs text-red-500 mb-2">{{ form.errors.branch_ids }}</p>
                    <div class="space-y-2">
                        <label
                            v-for="branch in branches"
                            :key="branch.id"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition-colors"
                            :class="form.branch_ids.includes(branch.id) ? 'border-[#FF5722] bg-orange-50' : 'border-gray-100 bg-white'"
                        >
                            <input
                                type="checkbox"
                                :checked="form.branch_ids.includes(branch.id)"
                                @change="toggleBranch(branch.id)"
                                class="rounded border-gray-300 text-[#FF5722] focus:ring-[#FF5722]/30"
                            />
                            <span class="text-sm font-medium text-gray-800">{{ branch.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <Link :href="route('settings.users')" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancelar</Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl px-6 py-2.5 text-sm disabled:opacity-60 transition-colors"
                    >
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </SettingsLayout>
    </AppLayout>
</template>
