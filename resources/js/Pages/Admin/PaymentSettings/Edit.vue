<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    settings: {
        bank_name: string | null;
        account_name: string | null;
        account_number: string | null;
        qr_image_url: string | null;
        instructions: { en: string; ms: string };
    };
}>();

const form = useForm({
    bank_name: props.settings.bank_name ?? '',
    account_name: props.settings.account_name ?? '',
    account_number: props.settings.account_number ?? '',
    instructions: { ...props.settings.instructions },
    qr_image: null as File | null,
});

const onQrChange = (event: Event) => {
    form.qr_image = (event.target as HTMLInputElement).files?.[0] ?? null;
};

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.instructions.ms = form.instructions.en;
    form.put(route('admin.payment-settings.update'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Payment settings" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Payment settings
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="bank_name" value="Bank name" />
                <TextInput
                    id="bank_name"
                    v-model="form.bank_name"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.bank_name" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="account_name" value="Account name" />
                    <TextInput
                        id="account_name"
                        v-model="form.account_name"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.account_name" />
                </div>

                <div>
                    <InputLabel for="account_number" value="Account number" />
                    <TextInput
                        id="account_number"
                        v-model="form.account_number"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.account_number" />
                </div>
            </div>

            <div>
                <InputLabel for="qr_image" value="DuitNow QR image" />
                <img
                    v-if="settings.qr_image_url"
                    :src="settings.qr_image_url"
                    alt="Current QR"
                    class="mt-2 h-32 w-32 rounded border border-gray-200 object-contain dark:border-gray-700"
                />
                <input
                    id="qr_image"
                    type="file"
                    accept="image/*"
                    class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-400"
                    @change="onQrChange"
                />
                <InputError class="mt-2" :message="form.errors.qr_image" />
            </div>

            <div>
                <InputLabel for="instructions_en" value="Payment instructions" />
                <textarea
                    id="instructions_en"
                    v-model="form.instructions.en"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                ></textarea>
                <InputError class="mt-2" :message="form.errors['instructions.en']" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Save
                </PrimaryButton>
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                    Saved.
                </p>
            </div>
        </form>
    </AdminLayout>
</template>
