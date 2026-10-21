<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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
    <CustomerLayout>
        <Head title="Check your due date" />

        <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
            When is baby due?
        </h1>

        <form class="space-y-4" @submit.prevent="submit">
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

            <div
                v-if="full"
                class="rounded-md bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-900/30 dark:text-amber-200"
            >
                <p>That date is fully booked.</p>
                <p v-if="nextAvailable" class="mt-2">
                    Next available:
                    <button type="button" class="font-medium underline" @click="useNextAvailable">
                        {{ nextAvailable }}
                    </button>
                </p>
            </div>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Check availability
            </PrimaryButton>
        </form>
    </CustomerLayout>
</template>
