<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface BookingRow {
    id: number;
    ref_no: string;
    edd: string;
    price_sen: number;
    customer: { name: string };
    variant: { package: { name: Record<string, string> } };
}

defineProps<{
    bookings: {
        data: BookingRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const money = (sen: number) => (sen / 100).toFixed(2);
const rejecting = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });

const approve = (id: number) => useForm({}).post(route('admin.booking-approvals.approve', id));

const reject = (id: number) => {
    rejectForm.post(route('admin.booking-approvals.reject', id), {
        onSuccess: () => {
            rejecting.value = null;
            rejectForm.reset();
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Booking approvals" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Booking approvals
        </h1>

        <div class="space-y-4">
            <div
                v-for="booking in bookings.data"
                :key="booking.id"
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ booking.customer.name }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ booking.variant.package.name.en }} · EDD {{ formatDate(booking.edd) }} · RM
                            {{ money(booking.price_sen) }}
                        </p>
                        <p class="font-mono text-xs text-gray-400">{{ booking.ref_no }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('admin.bookings.show', booking.id)">
                            <SecondaryButton type="button">View</SecondaryButton>
                        </Link>
                        <PrimaryButton @click="approve(booking.id)">Approve</PrimaryButton>
                        <DangerButton
                            @click="rejecting = rejecting === booking.id ? null : booking.id"
                        >
                            Reject
                        </DangerButton>
                    </div>
                </div>

                <div v-if="rejecting === booking.id" class="mt-3 flex items-center gap-2">
                    <TextInput
                        v-model="rejectForm.reason"
                        type="text"
                        placeholder="Reason for rejection"
                        class="flex-1"
                    />
                    <DangerButton @click="reject(booking.id)">Confirm reject</DangerButton>
                </div>
            </div>

            <p v-if="bookings.data.length === 0" class="text-center text-gray-600">
                No bookings awaiting approval.
            </p>
        </div>

        <Pagination :links="bookings.links" />
    </AdminLayout>
</template>
