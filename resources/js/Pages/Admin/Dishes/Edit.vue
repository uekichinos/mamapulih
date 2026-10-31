<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    dish: {
        id: number;
        name: { en: string; ms: string };
        tags: string[];
        substitute_dish_id: number | null;
        active: boolean;
    };
    dishes: Array<{ id: number; name: { en: string; ms: string } }>;
    tags: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    name: { ...props.dish.name },
    tags: [...props.dish.tags],
    substitute_dish_id: props.dish.substitute_dish_id,
    active: props.dish.active,
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.put(route('admin.dishes.update', props.dish.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit dish" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Edit dish
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name_en" value="Name" />
                <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>

            <div>
                <InputLabel value="Tags" />
                <div class="mt-2 flex flex-wrap gap-3">
                    <label
                        v-for="tag in tags"
                        :key="tag.value"
                        class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300"
                    >
                        <input v-model="form.tags" type="checkbox" :value="tag.value" />
                        {{ tag.label }}
                    </label>
                </div>
            </div>

            <div>
                <InputLabel
                    for="substitute_dish_id"
                    value="Substitute dish (used when a customer excludes a tag above)"
                />
                <SelectInput id="substitute_dish_id" v-model="form.substitute_dish_id" class="mt-1">
                    <option :value="null">None</option>
                    <option v-for="candidate in dishes" :key="candidate.id" :value="candidate.id">
                        {{ candidate.name.en }}
                    </option>
                </SelectInput>
                <InputError class="mt-2" :message="form.errors.substitute_dish_id" />
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
