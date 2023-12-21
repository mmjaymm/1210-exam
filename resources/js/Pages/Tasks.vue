<script setup>
import { ref, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import TextArea from "@/Components/TextArea.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Swal from "sweetalert2";

let task = ref({
    name: "",
    description: "",
    status: "todo",
    documents: null,
});

let tasks = ref([]);

let taskAttachments = ref(null);
let selectedTask = ref(null);
let searchTerm = ref(null);

onMounted(async () => {
    getTasks();
});

const submitTask = async (e) => {
    const formData = new FormData(e.target);
    let files = taskAttachments.value.files;
    let url = "/api/task/create";

    if (files.length) {
        for (let i = 0; i < files.length; i++) {
            formData.append("documents[]", files[i]);
        }
    } else {
        formData.delete("documents");
    }

    if (selectedTask.value !== null) {
        formData.append("_method", "PUT");
        url = `/api/task/${selectedTask.value}/update`;
    }
    // //Add
    await axios
        .post(url, formData, {
            headers: { "Content-Type": "multipart/form-data" },
        })
        .then(async (response) => {
            if (
                (response.status === 201 || response.status === 200) &&
                response.data.status
            ) {
                formReset();
                await getTasks();
            }
        })
        .catch((err) => console.error(err));
};

const getTasks = async () => {
    let response = await axios.get("/api/task/get-all");
    tasks.value = response.data.data.data;
};

const deleteTask = async (taskId) => {
    Swal.fire({
        title: "Are you sure you want to delete this task?",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios
                .delete(`/api/task/${taskId}`)
                .then((response) => {
                    if (response.status === 200 && response.data.status) {
                        tasks.value = tasks.value.filter(
                            (task) => task.id != taskId
                        );
                        Swal.fire("Deleted!", "", "success");
                    }
                })
                .catch((err) => console.error(err));
        }
    });
};

const editTask = async (taskId) => {
    await axios
        .get(`/api/task/${taskId}`)
        .then((response) => {
            if (response.status === 200 && response.data.status) {
                task.value = response.data.data;
                selectedTask.value = taskId;
            }
        })
        .catch((err) => console.error(err));
};

const cancelEdit = () => {
    Swal.fire({
        title: "Are you sure you want to cancel editing?",
        showCancelButton: true,
        confirmButtonText: "Yes",
    }).then(async (result) => {
        if (result.isConfirmed) {
            formReset();
        }
    });
};

const formReset = () => {
    task.value = {
        name: "",
        description: "",
        status: "todo",
        documents: null,
    };

    taskAttachments.value = null;

    selectedTask.value = null;
};

const searchTasks = async () => {
    if (searchTerm.value) {
        let response = await axios.get("/api/task/get-all", {
            params: { search: searchTerm.value },
        });
        tasks.value = response.data.data.data;
    } else {
        await getTasks();
    }
};
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tasks Management
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex">
                    <div
                        class="w-full sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg mr-6"
                    >
                        <form
                            @submit.prevent="submitTask"
                            enctype="multipart/form-data"
                        >
                            <div>
                                <InputLabel for="name" value="Task" />

                                <TextInput
                                    v-model="task.name"
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel
                                    for="description"
                                    value="Description"
                                />

                                <TextArea
                                    v-model="task.description"
                                    id="description"
                                    name="description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="description"
                                />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="status" value="Status" />

                                <select
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    name="status"
                                    id="status"
                                    v-model="task.status"
                                >
                                    <option value="todo" selected>Todo</option>
                                    <option value="in progress">
                                        In Progress
                                    </option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <InputLabel for="documents" value="Documents" />
                                <input
                                    id="documents"
                                    name="documents"
                                    type="file"
                                    ref="taskAttachments"
                                    multiple
                                />
                            </div>
                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': task.processing }"
                                    :disabled="task.processing"
                                >
                                    {{
                                        !selectedTask
                                            ? "Create Task"
                                            : "Update Task"
                                    }}
                                </PrimaryButton>
                            </div>
                        </form>
                        <DangerButton
                            class="ms-4"
                            @click="cancelEdit"
                            v-if="selectedTask"
                        >
                            Cancel Edit
                        </DangerButton>
                    </div>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-5">
                            <label
                                for="search"
                                class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white"
                                >Search</label
                            >
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"
                                >
                                    <svg
                                        class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                        aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                                        />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchTerm"
                                    type="search"
                                    id="search"
                                    name="search"
                                    class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Search Task"
                                    required
                                />
                                <button
                                    @click="searchTasks"
                                    type="button"
                                    class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                        <table
                            class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
                        >
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400"
                            >
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Task Name
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Task Description
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Date Created
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-center"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="task in tasks"
                                    :key="task.id"
                                    v-if="tasks.length > 0"
                                    class="bg-white border-b"
                                >
                                    <td class="px-6 py-4">{{ task.name }}</td>
                                    <td class="px-6 py-4">
                                        {{ task.description }}
                                    </td>
                                    <td class="px-6 py-4">{{ task.status }}</td>
                                    <td class="px-6 py-4 text-nowrap">
                                        {{ task.created_at }}
                                    </td>
                                    <td class="px-6 py-4 text-nowrap">
                                        <button
                                            type="button"
                                            class="text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2"
                                            @click="deleteTask(task.id)"
                                        >
                                            Delete
                                        </button>
                                        <button
                                            type="button"
                                            class="text-white bg-yellow-400 hover:bg-yellow-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 font-medium rounded-full text-sm px-5 py-2.5 text-center me-2 mb-2"
                                            @click="editTask(task.id)"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr v-else class="bg-white border-b">
                                    <td class="text-center" colspan="5">
                                        No Record Found !
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
