import {
    HomeIcon,
    InboxIcon,
    DocumentTextIcon,
    UsersIcon,
    UserIcon,
    ArrowLeftOnRectangleIcon
} from '@heroicons/vue/24/outline';

export const navigationItems = [
    {
        name: 'Beranda',
        href: 'users.index',
        icon: HomeIcon,
        description: 'Dashboard utama sistem'
    },
    {
        name: 'Surat Masuk',
        href: 'surat-masuk.index',
        icon: InboxIcon,
        description: 'Manajemen surat masuk'
    },
    {
        name: 'Surat Keluar',
        href: 'surat-keluar.index',
        icon: DocumentTextIcon,
        description: 'Manajemen surat keluar'
    }
];

export const adminItems = [
    {
        name: 'Pengguna',
        href: 'users.pengguna',
        icon: UsersIcon,
        description: 'Manajemen pengguna sistem'
    }
];

export const userMenuItems = [
    {
        name: 'Profile',
        href: 'users.profile',
        icon: UserIcon,
    },
    {
        name: 'Logout',
        href: 'logout',
        icon: ArrowLeftOnRectangleIcon,
        method: 'post'
    }
];