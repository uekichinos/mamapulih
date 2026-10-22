<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    draft: Record<string, unknown>;
    dietTags: { value: string; label: string }[];
}>();

const form = useForm({
    address: {
        line1: '',
        line2: '',
        postcode: '',
        notes: '',
    },
    diet: {
        allergies: [] as string[],
        exclusions: [] as string[],
        notes: '',
    },
});

const submit = () => form.post(route('customer.booking.details'));
</script>

<template>
    <CustomerLayout>
        <Head title="Delivery address and diet" />

        <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
            Delivery address and diet
        </h1>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="line1" value="Address" />
                <TextInput id="line1" v-model="form.address.line1" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['address.line1']" />
            </div>

            <div>
                <InputLabel for="line2" value="Address (line 2, optional)" />
                <TextInput id="line2" v-model="form.address.line2" class="mt-1 block w-full" />
            </div>

            <div>
                <InputLabel for="postcode" value="Postcode" />
                <TextInput id="postcode" v-model="form.address.postcode" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['address.postcode']" />
            </div>

            <div>
                <InputLabel for="address-notes" value="Delivery notes (optional)" />
                <TextInput id="address-notes" v-model="form.address.notes" class="mt-1 block w-full" />
            </div>

            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                <InputLabel value="Allergies" />
                <div class="mt-2 flex flex-wrap gap-3">
                    <label v-for="tag in dietTags" :key="tag.value" class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.diet.allergies" type="checkbox" :value="tag.value" />
                        {{ tag.label }}
                    </label>
                </div>
            </div>

            <div>
                <InputLabel value="Food exclusions / dislikes" />
                <div class="mt-2 flex flex-wrap gap-3">
                    <label v-for="tag in dietTags" :key="tag.value" class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.diet.exclusions" type="checkbox" :value="tag.value" />
                        {{ tag.label }}
                    </label>
                </div>
            </div>

            <div>
                <InputLabel for="diet-notes" value="Diet notes (optional)" />
                <TextInput id="diet-notes" v-model="form.diet.notes" class="mt-1 block w-full" />
            </div>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Continue
            </PrimaryButton>
        </form>
    </CustomerLayout>
</template>
