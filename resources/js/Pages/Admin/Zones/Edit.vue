<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    zone: {
        id: number;
        name: string;
        postcodes: string[];
        free_delivery: boolean;
        transport_cost_per_trip_sen: number;
        one_trip_allowed: boolean;
    };
}>();

const postcodesInput = ref(props.zone.postcodes.join(', '));

const form = useForm({
    name: props.zone.name,
    postcodes: [...props.zone.postcodes],
    free_delivery: props.zone.free_delivery,
    transport_cost_per_trip_sen: props.zone.transport_cost_per_trip_sen,
    one_trip_allowed: props.zone.one_trip_allowed,
});

const submit = () => {
    form.postcodes = postcodesInput.value
        .split(',')
        .map((p) => p.trim())
        .filter(Boolean);

    form.put(route('admin.zones.update', props.zone.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit zone" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Edit zone
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Zone name" />
                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="postcodes" value="Postcodes (comma-separated)" />
                <TextInput
                    id="postcodes"
                    v-model="postcodesInput"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.postcodes" />
            </div>

            <label class="flex items-center gap-2">
                <input
                    v-model="form.free_delivery"
                    type="checkbox"
                    class="rounded border-gray-300"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Free delivery</span>
            </label>

            <label class="flex items-center gap-2">
                <input
                    v-model="form.one_trip_allowed"
                    type="checkbox"
                    class="rounded border-gray-300"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    One-trip delivery allowed (v1.1)
                </span>
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
