{/* Index.vue */}
<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import {
    MagnifyingGlassIcon,
    PlusIcon,
    EnvelopeIcon,
    EnvelopeOpenIcon,
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    DocumentIcon,
    ArrowDownTrayIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

// Props
const props = defineProps({
    suratMasuk: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            start_date: '',
            end_date: '',
            per_page: 10
        })
    },
    permissions: {
        type: Object,
        required: true,
        default: () => ({
            canCreate: false,
            canEdit: false,
            canDelete: false,
            canView: true
        })
    }
});

// Refs for search, filters, and pagination
const search = ref('');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const perPage = ref(props.filters.per_page?.toString() || '10');
const sortField = ref('tgl_sm');
const sortDirection = ref('desc');

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showViewModal = ref(false);
const selectedSurat = ref(null);

// File states
const previewUrl = ref('');
const fileInput = ref(null);
const uploadMethod = ref('file');
const video = ref(null);
const imageCaptured = ref(false);
const capturedImage = ref(null);
let stream = null;

// Pagination options
const perPageOptions = [
    { label: '10', value: '10' },
    { label: '15', value: '15' },
    { label: '25', value: '25' },
    { label: '50', value: '50' }
];

// Forms
const createForm = useForm({
    no_asal: '',
    tgl_no_asal: '',
    penerima: '',
    pengirim: '',
    perihal: '',
    lampiran: null
});

const editForm = useForm({
    tgl_no_asal: '',
    penerima: '',
    pengirim: '',
    perihal: ''
});

// Computed Properties
const filteredSuratMasuk = computed(() => {
    if (!search.value) return props.suratMasuk.data;

    const searchTerm = search.value.toLowerCase();
    return props.suratMasuk.data.filter(surat =>
        surat.no_surat?.toLowerCase().includes(searchTerm) ||
        surat.perihal?.toLowerCase().includes(searchTerm) ||
        surat.pengirim?.toLowerCase().includes(searchTerm)
    );
});

// Methods
const updateFilters = () => {
    router.get(
        route('surat-masuk.index'),
        {
            search: search.value,
            start_date: startDate.value,
            end_date: endDate.value,
            per_page: perPage.value,
            sort_field: sortField.value,
            sort_direction: sortDirection.value,
            page: 1, // Reset to first page when filters change
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        createForm.lampiran = file;
        if (file.type.startsWith('image/')) {
            previewUrl.value = URL.createObjectURL(file);
        } else {
            previewUrl.value = '';
        }
    }
};

const submitCreate = () => {
    createForm.post(route('surat-masuk.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
            previewUrl.value = '';
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const editSurat = (surat) => {
    selectedSurat.value = surat;
    editForm.tgl_no_asal = surat.tgl_no_asal;
    editForm.penerima = surat.penerima;
    editForm.pengirim = surat.pengirim;
    editForm.perihal = surat.perihal;
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(route('surat-masuk.update', selectedSurat.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedSurat.value = null;
        },
    });
};

const viewSurat = (surat) => {
    selectedSurat.value = surat;
    showViewModal.value = true;
};

const confirmDelete = (surat) => {
    selectedSurat.value = surat;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    useForm({}).delete(route('surat-masuk.destroy', selectedSurat.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedSurat.value = null;
        },
    });
};

const toggleSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    updateFilters();
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const parts = dateString.split('-');
    if (parts.length !== 3) return dateString;
    const date = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

// Camera handling
const isMobile = () => {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    if (video.value) {
        video.value.srcObject = null;
    }
};

const captureImage = () => {
    const canvas = document.createElement('canvas');
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    canvas.getContext('2d').drawImage(video.value, 0, 0);
    capturedImage.value = canvas.toDataURL('image/jpeg');
    imageCaptured.value = true;
};

const retakePhoto = () => {
    imageCaptured.value = false;
    capturedImage.value = null;
};

const usePhoto = () => {
    fetch(capturedImage.value)
        .then(res => res.blob())
        .then(blob => {
            const file = new File([blob], "captured-image.jpg", { type: "image/jpeg" });
            createForm.lampiran = file;
            previewUrl.value = URL.createObjectURL(file);
        });
    stopCamera();
};

// Watchers
watch(uploadMethod, async (newValue) => {
    if (newValue === 'camera') {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: isMobile() ? 'environment' : 'user' }
            });
            if (video.value) {
                video.value.srcObject = stream;
            }
        } catch (err) {
            console.error('Error accessing camera:', err);
        }
    } else {
        stopCamera();
    }
});

let searchTimeout;
watch(search, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 300); // Debounce search for 300ms
});

watch([perPage, startDate, endDate], () => {
    updateFilters();
});

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
    stopCamera();
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Surat Masuk" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Surat Masuk
                </h2>
                <button
                    v-if="permissions.canCreate"
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="w-5 h-5" />
                    Tambah Surat
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filters Section -->
                <div class="mb-6 bg-white rounded-lg shadow p-4 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 flex flex-col sm:flex-row gap-4">
                        <!-- Search input -->
                        <div class="relative flex-1">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Cari surat masuk..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        <!-- Date range filter -->
                        <div class="flex items-center space-x-2">
                            <input
                                type="date"
                                v-model="startDate"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <span class="text-gray-500">to</span>
                            <input
                                type="date"
                                v-model="endDate"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                        </div>

                        <!-- Entries per page dropdown -->
                        <div class="flex items-center space-x-2">
                            <select
                                v-model="perPage"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option v-for="option in perPageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No
                                    </th>
                                    <th
                                        @click="toggleSort('no_surat')"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    >
                                        No. Surat
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Perihal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pengirim
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Penerima
                                    </th>
                                    <th
                                        @click="toggleSort('tgl_no_asal')"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    >
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(surat, index) in filteredSuratMasuk"
                                    :key="surat.id"
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ (suratMasuk.current_page - 1) * suratMasuk.per_page + index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ surat.no_surat }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 line-clamp-2">
                                            {{ surat.perihal }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ surat.pengirim }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ surat.penerima }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            {{ formatDate(surat.tgl_no_asal) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="viewSurat(surat)"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Lihat detail"
                                            >
                                                <EyeIcon class="w-5 h-5" />
                                            </button>
                                            <button
                                                v-if="permissions.canEdit"
                                                @click="editSurat(surat)"
                                                class="text-yellow-600 hover:text-yellow-900"
                                                title="Edit surat"
                                            >
                                                <PencilSquareIcon class="w-5 h-5" />
                                            </button>
                                            <button
                                                v-if="permissions.canDelete"
                                                @click="confirmDelete(surat)"
                                                class="text-red-600 hover:text-red-900"
                                                title="Hapus surat"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link
                            v-if="suratMasuk.prev_page_url"
                            :href="suratMasuk.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="suratMasuk.next_page_url"
                            :href="suratMasuk.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ suratMasuk.from }}</span>
                                to
                                <span class="font-medium">{{ suratMasuk.to }}</span>
                                of
                                <span class="font-medium">{{ suratMasuk.total }}</span>
                                results
                            </p>
                        </div>
                        <Pagination :links="suratMasuk.links" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredSuratMasuk.length === 0"
                    class="text-center py-12 bg-white rounded-lg shadow mt-6"
                >
                    <EnvelopeIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        Tidak ada surat masuk
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ search ? 'Tidak ada surat yang sesuai dengan pencarian.' : 'Mulai dengan menambahkan surat masuk baru.' }}
                    </p>
                    <div class="mt-6">
                        <button
                            v-if="permissions.canCreate"
                            @click="showCreateModal = true"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        >
                            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                            Tambah Surat Masuk
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" title="Tambah Surat Masuk" @close="showCreateModal = false">
            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <InputLabel for="no_asal" value="Nomor Surat" />
                    <TextInput
                        id="no_asal"
                        type="text"
                        v-model="createForm.no_asal"
                        class="mt-1 block w-full"
                        placeholder="Masukkan nomor surat"
                        required
                    />
                    <InputError :message="createForm.errors.no_asal" />
                </div>

                <div>
                    <InputLabel for="tgl_no_asal" value="Tanggal Surat" />
                    <TextInput
                        id="tgl_no_asal"
                        type="date"
                        v-model="createForm.tgl_no_asal"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="createForm.errors.tgl_no_asal" />
                </div>

                <div>
                    <InputLabel for="penerima" value="Penerima" />
                    <TextInput
                        id="penerima"
                        v-model="createForm.penerima"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Masukkan nama penerima"
                        required
                    />
                    <InputError :message="createForm.errors.penerima" />
                </div>

                <div>
                    <InputLabel for="pengirim" value="Pengirim" />
                    <TextInput
                        id="pengirim"
                        v-model="createForm.pengirim"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Masukkan nama pengirim"
                        required
                    />
                    <InputError :message="createForm.errors.pengirim" />
                </div>

                <div>
                    <InputLabel for="perihal" value="Perihal" />
                    <textarea
                        id="perihal"
                        v-model="createForm.perihal"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                        rows="3"
                        placeholder="Masukkan perihal surat"
                        required
                    ></textarea>
                    <InputError :message="createForm.errors.perihal" />
                </div>

                <div>
                    <InputLabel value="Metode Upload Lampiran" />
                    <div class="mt-2 space-y-4">
                        <!-- Upload Method Selection -->
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input
                                    type="radio"
                                    v-model="uploadMethod"
                                    value="file"
                                    class="form-radio text-blue-600"
                                >
                                <span class="ml-2">Upload File</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    type="radio"
                                    v-model="uploadMethod"
                                    value="camera"
                                    class="form-radio text-blue-600"
                                >
                                <span class="ml-2">Ambil Foto</span>
                            </label>
                        </div>

                        <!-- File Upload Section -->
                        <div v-if="uploadMethod === 'file'" class="mt-3">
                            <input
                                type="file"
                                id="lampiran"
                                ref="fileInput"
                                @change="handleFileChange"
                                class="hidden"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            />
                            <label
                                for="lampiran"
                                class="flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                            >
                                <DocumentIcon class="w-5 h-5 mr-2" />
                                <span>Pilih File</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ createForm.lampiran ? createForm.lampiran.name : 'Tidak ada file dipilih' }}
                            </p>
                        </div>

                        <!-- Camera Capture Section -->
                        <div v-if="uploadMethod === 'camera'" class="mt-3">
                            <div class="space-y-4">
                                <!-- Camera Preview -->
                                <div v-if="!imageCaptured" class="relative">
                                    <video
                                        ref="video"
                                        class="w-full rounded-lg border border-gray-300"
                                        autoplay
                                        playsinline
                                    ></video>
                                    <button
                                        @click="captureImage"
                                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Ambil Foto
                                    </button>
                                </div>

                                <!-- Captured Image Preview -->
                                <div v-if="imageCaptured" class="space-y-3">
                                    <img
                                        :src="capturedImage"
                                        class="w-full rounded-lg border border-gray-300"
                                        alt="Captured"
                                    />
                                    <div class="flex justify-center space-x-3">
                                        <button
                                            @click="retakePhoto"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
                                        >
                                            Ambil Ulang
                                        </button>
                                        <button
                                            @click="usePhoto"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                        >
                                            Gunakan Foto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview -->
                        <div v-if="previewUrl" class="mt-3">
                            <img
                                :src="previewUrl"
                                class="max-w-xs rounded-lg shadow-sm"
                                alt="Preview"
                            />
                        </div>
                    </div>
                    <InputError :message="createForm.errors.lampiran" />
                </div>
            </form>

            <template #footer>
                <SecondaryButton @click="showCreateModal = false">
                    Batal
                </SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :disabled="createForm.processing"
                    @click="submitCreate"
                >
                    {{ createForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </PrimaryButton>
            </template>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" title="Edit Surat Masuk" @close="showEditModal = false">
            <form @submit.prevent="submitEdit" class="space-y-4">
                <div>
                    <InputLabel for="edit_tgl_no_asal" value="Tanggal Surat" />
                    <TextInput
                        id="edit_tgl_no_asal"
                        type="date"
                        v-model="editForm.tgl_no_asal"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="editForm.errors.tgl_no_asal" />
                </div>

                <div>
                    <InputLabel for="edit_penerima" value="Penerima" />
                    <TextInput
                        id="edit_penerima"
                        v-model="editForm.penerima"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Masukkan nama penerima"
                        required
                    />
                    <InputError :message="editForm.errors.penerima" />
                </div>

                <div>
                    <InputLabel for="edit_pengirim" value="Pengirim" />
                    <TextInput
                        id="edit_pengirim"
                        v-model="editForm.pengirim"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Masukkan nama pengirim"
                        required
                    />
                    <InputError :message="editForm.errors.pengirim" />
                </div>

                <div>
                    <InputLabel for="edit_perihal" value="Perihal" />
                    <textarea
                        id="edit_perihal"
                        v-model="editForm.perihal"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                        rows="3"
                        required
                    ></textarea>
                    <InputError :message="editForm.errors.perihal" />
                </div>
            </form>

            <template #footer>
                <SecondaryButton @click="showEditModal = false">
                    Batal
                </SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :disabled="editForm.processing"
                    @click="submitEdit"
                >
                    {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
                </PrimaryButton>
            </template>
        </Modal>

        <!-- View Modal -->
        <Modal :show="showViewModal" title="Detail Surat Masuk" @close="showViewModal = false">
            <div class="space-y-4" v-if="selectedSurat">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Nomor Surat</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ selectedSurat.no_surat }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Tanggal Surat</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedSurat.tgl_ns) }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Pengirim</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ selectedSurat.pengirim }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Penerima</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ selectedSurat.penerima }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Perihal</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ selectedSurat.perihal }}</p>
                </div>

                <div v-if="selectedSurat.lampiran && selectedSurat.lampiran.length > 0">
                    <h4 class="text-sm font-medium text-gray-500">Lampiran</h4>
                    <div class="mt-1 flex items-center space-x-2">
                        <DocumentIcon class="h-5 w-5 text-gray-400" />
                        <span class="text-sm text-gray-900">{{ selectedSurat.lampiran[0].nama_berkas }}</span>
                        <a
                            :href="route('surat-masuk.download-lampiran', selectedSurat.id)"
                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                            download
                        >
                            <ArrowDownTrayIcon class="h-4 w-4 mr-1" />
                            Download
                        </a>
                    </div>
                </div>
            </div>

            <template #footer>
                <SecondaryButton @click="showViewModal = false">
                    Tutup
                </SecondaryButton>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" title="Konfirmasi Hapus" @close="showDeleteModal = false">
            <div class="p-6">
                <ExclamationTriangleIcon class="h-12 w-12 text-red-600 mx-auto" />
                <p class="mt-4 text-center text-gray-700">
                    Apakah Anda yakin ingin menghapus surat masuk ini?<br>
                    <span class="font-medium">Tindakan ini tidak dapat dibatalkan.</span>
                </p>
            </div>

            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Batal
                </SecondaryButton>
                <DangerButton
                    class="ml-3"
                    :disabled="createForm.processing"
                    @click="submitDelete"
                >
                    Hapus
                </DangerButton>
            </template>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transition for table row hover */
tr {
    transition: all 0.2s ease;
}

/* Custom scrollbar for better UX */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>