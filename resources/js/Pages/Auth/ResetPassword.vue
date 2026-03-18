<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    token: String,
    email: String,
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Nueva contrasena" />

    <div class="min-h-screen bg-[#FAFAFA] flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="flex flex-col items-center mb-8">
                <div class="bg-orange-50 p-3 rounded-2xl mb-3">
                    <span class="material-symbols-outlined text-[#FF5722] text-4xl" style="font-variation-settings:'FILL' 1">
                        local_fire_department
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">PideAqui</h1>
                <p class="text-sm text-gray-500 mt-1">Restablecer contrasena</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Nueva contrasena</h2>
                <p class="text-sm text-gray-500 mb-6">Ingresa tu nueva contrasena para recuperar el acceso a tu cuenta.</p>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email (readonly, prefilled from URL) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Correo electronico
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            readonly
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-500 bg-gray-50 cursor-not-allowed"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- New password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nueva contrasena
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                            required
                            autofocus
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722] transition-colors"
                            :class="{ 'border-red-400': form.errors.password }"
                            placeholder="Minimo 8 caracteres"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Confirmar contrasena
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FF5722]/30 focus:border-[#FF5722] transition-colors"
                            placeholder="Repite tu nueva contrasena"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#FF5722] hover:bg-[#D84315] text-white font-semibold rounded-xl py-2.5 text-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Restableciendo...' : 'Restablecer contrasena' }}
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <Link :href="route('login')" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors">
                        Volver al inicio de sesion
                    </Link>
                </div>
            </div>

        </div>
    </div>
</template>
