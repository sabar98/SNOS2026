<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    BookOpen,
    CalendarClock,
    ClipboardCheck,
    Coins,
    Crown,
    FileText,
    Folder,
    Globe,
    Landmark,
    LayoutGrid,
    Megaphone,
    Mic,
    Newspaper,
    PenTool,
    Presentation,
    QrCode,
    ScrollText,
    Tags,
    UserCheck,
    UserCog,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();
const roles = computed(() => page.props.auth.roles ?? []);

const navByRole: Record<string, NavItem[]> = {
    peserta: [
        { title: 'Dashboard', href: '/participant/dashboard', icon: LayoutGrid },
        { title: 'Profil', href: '/participant/profile', icon: UserCog },
        { title: 'Jadwal & Check-in', href: '/participant/schedule', icon: CalendarClock },
        { title: 'Sertifikat', href: '/participant/certificates', icon: BadgeCheck },
        { title: 'Pengumuman', href: '/participant/announcements', icon: Megaphone },
    ],
    admin: [
        { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
        { title: 'Peserta', href: '/admin/participants', icon: Users },
        { title: 'Check-in QR', href: '/admin/check-in', icon: QrCode },
        { title: 'Pembayaran', href: '/admin/payments', icon: Wallet },
        { title: 'Bank Rekening', href: '/admin/bank-accounts', icon: Landmark },
        { title: 'Kategori Peserta', href: '/admin/participant-categories', icon: Tags },
        { title: 'Biaya Pendaftaran', href: '/admin/registration-fees', icon: Coins },
        { title: 'Artikel', href: '/admin/articles', icon: FileText },
        { title: 'Tanda Tangan LoA', href: '/admin/loa-settings', icon: PenTool },
        { title: 'Penugasan Reviewer', href: '/admin/reviewer-assignments', icon: ClipboardCheck },
        { title: 'Moderator', href: '/admin/moderators', icon: Mic },
        { title: 'Reviewer', href: '/admin/reviewers', icon: UserCheck },
        { title: 'Pimpinan', href: '/admin/pimpinan', icon: Crown },
        { title: 'Narasumber', href: '/admin/narasumber', icon: Presentation },
        { title: 'Jadwal Sesi', href: '/admin/schedule-sessions', icon: CalendarClock },
        { title: 'Jurnal & Prosiding', href: '/admin/journals', icon: Newspaper },
        { title: 'Publikasi', href: '/admin/publications', icon: BookOpen },
        { title: 'Sertifikat', href: '/admin/certificates', icon: BadgeCheck },
        { title: 'Pengumuman', href: '/admin/announcements', icon: Megaphone },
        { title: 'Laporan', href: '/admin/reports', icon: ScrollText },
        { title: 'Landing Page', href: '/admin/landing-settings', icon: Globe },
    ],
    reviewer: [{ title: 'Artikel Ditugaskan', href: '/reviewer/articles', icon: FileText }],
    moderator: [{ title: 'Sesi Saya', href: '/moderator/sessions', icon: CalendarClock }],
    pimpinan: [{ title: 'Statistik', href: '/pimpinan/dashboard', icon: LayoutGrid }],
    narasumber: [{ title: 'Dashboard', href: '/narasumber/dashboard', icon: LayoutGrid }],
};

const mainNavItems = computed<NavItem[]>(() => {
    const items = roles.value.flatMap((role: string) => navByRole[role] ?? []);

    return items.length > 0 ? items : [{ title: 'Dashboard', href: '/dashboard', icon: LayoutGrid }];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
