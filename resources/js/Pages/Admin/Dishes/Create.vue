<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    dishes: Array<{ id: number; name: { en: string; ms: string } }>;
    tags: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    name: { en: '', ms: '' },
    tags: [] as string[],
    substitute_dish_id: null as number | null,
    active: true,
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.post(route('admin.dishes.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="New dish" />

        <PageHeader title="New dish">
            Tags let the kitchen auto-substitute this dish for a customer's allergies or exclusions.
        </PageHeader>

        <form @submit.prevent="submit">
            <div
                class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div>
                    <InputLabel for="name_en" value="Name" />
                    <TextInput
                        id="name_en"
                        v-model="form.name.en"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors['name.en']" />
                </div>

                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label
                            v-for="tag in tags"
                            :key="tag.value"
                            class="flex items-center gap-1.5 rounded-full border border-gray-200 px-3 py-1 text-sm text-gray-700 has-checked:border-indigo-200 has-checked:bg-indigo-50 has-checked:text-indigo-700 dark:border-gray-700 dark:text-gray-300 dark:has-checked:border-indigo-900 dark:has-checked:bg-indigo-950 dark:has-checked:text-indigo-300"
                        >
                            <input
                                v-model="form.tags"
                                type="checkbox"
                                :value="tag.value"
                                class="rounded border-gray-300"
                            />
                            {{ tag.label }}
                        </label>
                    </div>
                </div>

                <div>
                    <InputLabel
                        for="substitute_dish_id"
                        value="Substitute dish (used when a customer excludes a tag above)"
                    />
                    <SelectInput
                        id="substitute_dish_id"
                        v-model="form.substitute_dish_id"
                        class="mt-1"
                    >
                        <option :value="null">None</option>
                        <option v-for="dish in dishes" :key="dish.id" :value="dish.id">
                            {{ dish.name.en }}
                        </option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.substitute_dish_id" />
                </div>

                <label class="flex items-center gap-2">
                    <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>

            <div class="mt-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
