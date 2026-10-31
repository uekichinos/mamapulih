<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    enabled: boolean;
    required: boolean;
    secret?: string;
    qrCodeSvg?: string;
}>();

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('two-factor.setup.confirm'), {
        onFinish: () => form.reset('code'),
    });
};

const disable = () => {
    if (confirm('Turn off two-factor authentication for your account?')) {
        useForm({}).delete(route('two-factor.setup.destroy'));
    }
};
</script>

<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
                Two-factor authentication
            </h2>
        </template>

        <Head title="Two-factor authentication" />

        <div class="py-12">
            <div class="mx-auto max-w-xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <template v-if="enabled">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Two-factor authentication is turned on for your account. You'll be asked
                            for a code from your authenticator app each time you log in.
                        </p>

                        <p v-if="required" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            It can't be turned off for the Owner account.
                        </p>

                        <PrimaryButton
                            v-else
                            class="mt-4 bg-red-600 hover:bg-red-700"
                            @click="disable"
                        >
                            Turn off
                        </PrimaryButton>
                    </template>

                    <template v-else>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <span v-if="required">
                                The Owner account requires two-factor authentication. Scan this code
                                with an authenticator app (such as Google Authenticator or Authy),
                                then enter the 6-digit code it shows to finish setup.
                            </span>
                            <span v-else>
                                Scan this code with an authenticator app, then enter the 6-digit
                                code it shows to turn on two-factor authentication.
                            </span>
                        </p>

                        <!-- eslint-disable-next-line vue/no-v-html -- server-rendered QR SVG, not user input -->
                        <div class="mt-4 w-48" v-html="qrCodeSvg"></div>

                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                            Can't scan it? Enter this key manually:
                            <code class="font-mono">{{ secret }}</code>
                        </p>

                        <form class="mt-6 max-w-xs" @submit.prevent="submit">
                            <InputLabel for="code" value="6-digit code" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                class="mt-1 block w-full"
                                autofocus
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.code" />

                            <PrimaryButton
                                class="mt-4"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Confirm
                            </PrimaryButton>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
