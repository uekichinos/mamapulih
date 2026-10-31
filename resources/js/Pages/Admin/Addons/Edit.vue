<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    addon: {
        id: number;
        name: { en: string; ms: string };
        price_sen: number;
        per: 'day' | 'booking';
        active: boolean;
    };
}>();

const form = useForm({
    name: { ...props.addon.name },
    price_sen: props.addon.price_sen,
    per: props.addon.per,
    active: props.addon.active,
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.put(route('admin.addons.update', props.addon.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit add-on" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Edit add-on
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name_en" value="Name" />
                <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="price_sen" value="Price (RM)" />
                    <TextInput
                        id="price_sen"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        :model-value="form.price_sen / 100"
                        @update:model-value="(v) => (form.price_sen = Math.round(Number(v) * 100))"
                    />
                    <InputError class="mt-2" :message="form.errors.price_sen" />
                </div>

                <div>
                    <InputLabel for="per" value="Charged per" />
                    <SelectInput id="per" v-model="form.per" class="mt-1">
                        <option value="booking">Booking</option>
                        <option value="day">Day</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.per" />
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>

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
