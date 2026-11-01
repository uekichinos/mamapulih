<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
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

        <PageHeader title="Payment settings">
            How customers pay by manual bank transfer, shown on their payment page.
        </PageHeader>

        <form class="space-y-4" @submit.prevent="submit">
            <SectionCard
                title="Bank account"
                description="Where customers transfer manual payments to."
                tone="indigo"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="3" y="6" width="18" height="12" rx="2" />
                        <path d="M3 10h18M7 15h4" />
                    </svg>
                </template>

                <div class="space-y-4">
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
                </div>
            </SectionCard>

            <SectionCard
                title="DuitNow QR"
                description="Optional — shown alongside the bank details for customers who prefer to scan."
                tone="blue"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <path d="M14 14h3v3h-3zM18 18h3v3h-3zM14 21h3M21 14v3" />
                    </svg>
                </template>

                <div class="flex items-start gap-4">
                    <img
                        v-if="settings.qr_image_url"
                        :src="settings.qr_image_url"
                        alt="Current QR"
                        class="size-24 shrink-0 rounded-lg border border-gray-200 object-contain dark:border-gray-700"
                    />
                    <div
                        v-else
                        class="flex size-24 shrink-0 items-center justify-center rounded-lg border border-dashed border-gray-300 text-xs text-gray-400 dark:border-gray-700"
                    >
                        No QR yet
                    </div>
                    <div class="flex-1">
                        <InputLabel for="qr_image" value="Upload image" />
                        <input
                            id="qr_image"
                            type="file"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-400 dark:file:bg-indigo-950 dark:file:text-indigo-300"
                            @change="onQrChange"
                        />
                        <InputError class="mt-2" :message="form.errors.qr_image" />
                    </div>
                </div>
            </SectionCard>

            <SectionCard
                title="Instructions"
                description="Shown to customers on their payment page, below the account details."
                tone="amber"
            >
                <template #icon>
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M4 6h16M4 12h16M4 18h10" />
                    </svg>
                </template>

                <textarea
                    id="instructions_en"
                    v-model="form.instructions.en"
                    rows="3"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                ></textarea>
                <InputError class="mt-2" :message="form.errors['instructions.en']" />
            </SectionCard>

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
