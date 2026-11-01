<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import AlertBanner from '@/Components/AlertBanner.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    draft: { edd?: string } | null;
    full?: boolean;
    nextAvailable?: string | null;
}>();

const form = useForm({
    edd: props.draft?.edd ?? '',
});

const submit = () => form.post(route('customer.booking.slot'));

const useNextAvailable = () => {
    if (props.nextAvailable) {
        form.edd = props.nextAvailable;
        submit();
    }
};
</script>

<template>
    <CustomerAppLayout>
        <Head title="Check your due date" />

        <PageHeader title="When is baby due?" />

        <form
            class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            @submit.prevent="submit"
        >
            <div>
                <InputLabel for="edd" value="Expected delivery date (EDD)" />
                <TextInput
                    id="edd"
                    v-model="form.edd"
                    type="date"
                    class="mt-1 block w-full"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.edd" />
            </div>

            <AlertBanner v-if="full" tone="amber">
                <p>That date is fully booked.</p>
                <p v-if="nextAvailable" class="mt-2">
                    Next available:
                    <button type="button" class="font-medium underline" @click="useNextAvailable">
                        {{ nextAvailable }}
                    </button>
                </p>
            </AlertBanner>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Check availability
            </PrimaryButton>
        </form>
    </CustomerAppLayout>
</template>
