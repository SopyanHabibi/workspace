<script setup lang="ts">
import { Head, usePage, useForm } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";
import { Camera } from "lucide-vue-next";
import { computed, ref } from "vue";
import DeleteUser from "@/components/DeleteUser.vue";
import Heading from "@/components/Heading.vue";
import InputError from "@/components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { edit } from "@/routes/profile";
import { send } from "@/routes/verification";
import { update } from "@/actions/App/Http/Controllers/Settings/ProfileController";

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Profile settings", href: edit() }],
    },
});

const page = usePage();

interface AuthUser {
    name: string;
    username: string | null;
    email: string;
    email_verified_at: string | null;
    avatar_url: string;
}

const user = computed(() => {
    const auth = page.props.auth as unknown as { user: AuthUser };
    return auth.user;
});

const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);
const avatarError = ref("");

const profileForm = useForm({
    name: user.value.name,
    username: user.value.username ?? "",
    email: user.value.email,
});

function saveProfile() {
    profileForm.patch(update.url());
}

function triggerAvatarInput() {
    avatarInput.value?.click();
}

function handleAvatarChange(e: Event) {
    console.log('handleAvatarChange called');
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    console.log('File:', file?.name, file?.size);
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'Avatar must be under 2MB.';
        console.log('File too large');
        return;
    }

    console.log('Calling router.post');
    const formData = new FormData();
    formData.append('avatar', file);

    router.post('/settings/profile/avatar', formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => console.log('Upload success'),
        onError: (errors: Record<string, string>) => {
            console.log('Upload error:', errors);
            avatarError.value = errors.avatar ?? 'Failed to upload avatar.';
        },
    });
}
</script>

<template>
    <Head title="Profile settings" />
    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <!-- Avatar -->
        <div class="flex flex-col space-y-4">
            <Heading
                variant="small"
                title="Avatar"
                description="Click on your avatar to upload a new photo (max 2MB)"
            />
            <div class="flex items-center gap-4">
                <div class="relative">
                    <img
                        :src="avatarPreview ?? user.avatar_url"
                        :alt="user.name"
                        class="h-20 w-20 rounded-full object-cover ring-2 ring-border"
                    />
                    <button
                        @click="triggerAvatarInput"
                        class="absolute bottom-0 right-0 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-primary-foreground hover:bg-primary/90"
                    >
                        <Camera class="h-3 w-3" />
                    </button>
                    <input
                        ref="avatarInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="handleAvatarChange"
                    />
                </div>
                <div>
                    <p class="font-medium">{{ user.name }}</p>
                    <p
                        v-if="user.username"
                        class="text-sm text-muted-foreground"
                    >
                        @{{ user.username }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
            </div>
            <p v-if="avatarError" class="text-sm text-red-500">
                {{ avatarError }}
            </p>
        </div>

        <!-- Profile form -->
        <Heading
            variant="small"
            title="Profile"
            description="Update your name, username, and email address"
        />

        <form @submit.prevent="saveProfile" class="space-y-6">
            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    v-model="profileForm.name"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="profileForm.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="username">Username</Label>
                <div class="relative">
                    <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground"
                        >@</span
                    >
                    <Input
                        id="username"
                        v-model="profileForm.username"
                        class="mt-1 block w-full pl-7"
                        autocomplete="username"
                        placeholder="your_username"
                    />
                </div>
                <p class="text-xs text-muted-foreground">
                    Letters, numbers, dashes and underscores only. Max 30
                    characters.
                </p>
                <InputError
                    class="mt-2"
                    :message="profileForm.errors.username"
                />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="email"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="profileForm.errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="profileForm.processing">Save</Button>
            </div>
        </form>
    </div>

    <DeleteUser />
</template>
