<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface BookingRow {
    id: number;
    ref_no: string;
    edd: string;
    price_sen: number;
    customer: { name: string };
    variant: { package: { name: Record<string, string> } };
}

defineProps<{ bookings: BookingRow[] }>();

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

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Booking approvals
        </h1>

        <div class="space-y-4">
            <div
                v-for="booking in bookings"
                :key="booking.id"
                class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ booking.customer.name }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ booking.variant.package.name.en }} · EDD {{ booking.edd }} · RM
                            {{ money(booking.price_sen) }}
                        </p>
                        <p class="text-xs text-gray-400">{{ booking.ref_no }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <PrimaryButton @click="approve(booking.id)">Approve</PrimaryButton>
                        <DangerButton
                            @click="rejecting = rejecting === booking.id ? null : booking.id"
                        >
                            Reject
                        </DangerButton>
                    </div>
                </div>

                <div v-if="rejecting === booking.id" class="mt-3 flex items-center gap-2">
                    <input
                        v-model="rejectForm.reason"
                        type="text"
                        placeholder="Reason for rejection"
                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    />
                    <DangerButton @click="reject(booking.id)">Confirm reject</DangerButton>
                </div>
            </div>

            <p v-if="bookings.length === 0" class="text-center text-gray-500">
                No bookings awaiting approval.
            </p>
        </div>
    </AdminLayout>
</template>
