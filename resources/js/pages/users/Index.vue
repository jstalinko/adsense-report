<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { MoreHorizontal, PlusCircle, Pencil, Trash2 } from 'lucide-vue-next';

// Layouts & Types
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, User } from '@/types';

// Shadcn-Vue Components
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';

// --- PROPS & BREADCRUMBS ---
const props = defineProps<{ users: User[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manage Users',
        href: '/users'
    }
];

// --- STATE MANAGEMENT ---
const isDialogOpen = ref(false);
const isEditing = ref(false);
const userToDelete = ref<User | null>(null);

// Inertia Form Helper for Create/Update
const form = useForm({
    id: null as number | null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_admin: false,
});

// --- COMPUTED PROPERTIES ---
const dialogTitle = computed(() => isEditing.value ? 'Edit User' : 'Add New User');
const dialogDescription = computed(() => isEditing.value ? 'Update user details. Leave password blank to keep it unchanged.' : 'Create a new user account.');

// --- METHODS ---

// Open dialog for creating a new user
const openAddDialog = () => {
    isEditing.value = false;
    form.reset();
    isDialogOpen.value = true;
};

// Open dialog for editing an existing user
const openEditDialog = (user: User) => {
    isEditing.value = true;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.is_admin = !!user.is_admin; // Ensure it's a boolean
    form.password = ''; // Clear password fields for security
    form.password_confirmation = '';
    form.clearErrors();
    isDialogOpen.value = true;
};

// Handle form submission (Create or Update)
const submitForm = () => {
    if (isEditing.value) {
        // Update user
        form.post(route('user.update', { id: form.id }), {
            preserveScroll: true,
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    } else {
        // Create new user
        form.post(route('user.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    }
};

// Handle user deletion
const deleteUser = () => {
    if (userToDelete.value) {
        const deleteForm = useForm({});
        deleteForm.get(route('user.delete', { id: userToDelete.value.id }), {
            preserveScroll: true,
            onSuccess: () => {
                userToDelete.value = null;
            }
        });
    }
};

</script>

<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <div>
                        <CardTitle>Users</CardTitle>
                        <CardDescription>A list of all the users in your account.</CardDescription>
                    </div>
                    <!-- Add User Button & Dialog Trigger -->
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button @click="openAddDialog">
                                <PlusCircle class="w-4 h-4 mr-2" />
                                Add User
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="sm:max-w-[425px]">
                            <DialogHeader>
                                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                                <DialogDescription>{{ dialogDescription }}</DialogDescription>
                            </DialogHeader>
                            <form @submit.prevent="submitForm">
                                <div class="grid gap-4 py-4">
                                    <!-- Name -->
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="name" class="text-right">Name</Label>
                                        <div class="col-span-3">
                                            <Input id="name" v-model="form.name" class="w-full" />
                                            <div v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</div>
                                        </div>
                                    </div>
                                    <!-- Email -->
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="email" class="text-right">Email</Label>
                                         <div class="col-span-3">
                                            <Input id="email" type="email" v-model="form.email" class="w-full" />
                                            <div v-if="form.errors.email" class="text-sm text-red-500 mt-1">{{ form.errors.email }}</div>
                                        </div>
                                    </div>
                                    <!-- Password -->
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="password" class="text-right">Password</Label>
                                         <div class="col-span-3">
                                            <Input id="password" type="password" v-model="form.password" class="w-full" />
                                            <div v-if="form.errors.password" class="text-sm text-red-500 mt-1">{{ form.errors.password }}</div>
                                        </div>
                                    </div>
                                    <!-- Password Confirmation -->
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="password_confirmation" class="text-right">Confirm</Label>
                                         <div class="col-span-3">
                                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation" class="w-full" />
                                        </div>
                                    </div>
                                     <!-- Is Admin Checkbox -->
                                    <div class="grid grid-cols-4 items-center gap-4">
                                        <Label for="is_admin" class="text-right">Role</Label>
                                        <div class="col-span-3 flex items-center space-x-2">
                                            <Checkbox id="is_admin" :model-value="form.is_admin" @update:model-value="form.is_admin = !form.is_admin" />
                                            <label for="is_admin" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                                Set as Administrator
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button type="submit" :disabled="form.processing">
                                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Joined At</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="props.users.length > 0">
                            <TableRow v-for="user in props.users" :key="user.id">
                                <TableCell class="font-medium">{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_admin ? 'default' : 'secondary'">
                                        {{ user.is_admin ? 'Admin' : 'User' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ new Date(user.created_at).toLocaleDateString() }}</TableCell>
                                <TableCell class="text-right">
                                    <!-- Actions Dropdown -->
                                    <AlertDialog>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <span class="sr-only">Open menu</span>
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuItem @click="openEditDialog(user)">
                                                    <Pencil class="mr-2 h-4 w-4" />
                                                    <span>Edit</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <AlertDialogTrigger as-child>
                                                    <DropdownMenuItem @click="userToDelete = user" class="text-red-600 focus:text-red-600 focus:bg-red-50">
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        <span>Delete</span>
                                                    </DropdownMenuItem>
                                                </AlertDialogTrigger>
                                            </DropdownMenuContent>
                                        </DropdownMenu>

                                        <!-- Delete Confirmation Dialog -->
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This action cannot be undone. This will permanently delete the user account
                                                    <span class="font-medium">{{ userToDelete?.name }}</span> and remove their data from our servers.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel @click="userToDelete = null">Cancel</AlertDialogCancel>
                                                <AlertDialogAction @click="deleteUser" class="bg-red-600 hover:bg-red-700">
                                                    Yes, delete user
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                             <TableRow>
                                <TableCell colspan="5" class="h-24 text-center">
                                    No users found.
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </AppLayout>
</template>
