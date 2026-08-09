export const participantTypeLabels: Record<string, string> = {
    presenter_luring: 'Presenter Luring',
    presenter_daring: 'Presenter Daring',
    peserta_umum: 'Peserta Umum',
    peserta_mahasiswa: 'Peserta Mahasiswa',
};

export const articleStatusLabels: Record<string, string> = {
    draft: 'Draft',
    diajukan: 'Diajukan',
    pemeriksaan_administrasi: 'Pemeriksaan Administrasi',
    perlu_perbaikan_administrasi: 'Perlu Perbaikan Administrasi',
    ditolak_administrasi: 'Ditolak Administrasi',
    proses_review: 'Proses Review',
    sedang_direview: 'Sedang Direview',
    revisi_minor: 'Revisi Minor',
    revisi_mayor: 'Revisi Mayor',
    diterima: 'Diterima',
    ditolak: 'Ditolak',
};

export const paymentTypeLabels: Record<string, string> = {
    registrasi: 'Registrasi',
    publikasi: 'Publikasi',
};

export const paymentStatusLabels: Record<string, string> = {
    belum_bayar: 'Belum Bayar',
    menunggu_verifikasi: 'Menunggu Verifikasi',
    terverifikasi: 'Terverifikasi',
    perlu_perbaikan: 'Perlu Perbaikan',
};

export const registrationStatusLabels: Record<string, string> = {
    draft: 'Draft',
    menunggu_pembayaran: 'Menunggu Pembayaran',
    menunggu_verifikasi: 'Menunggu Verifikasi',
    pembayaran_terverifikasi: 'Pembayaran Terverifikasi',
    artikel_diajukan: 'Artikel Diajukan',
    sedang_direview: 'Sedang Direview',
    perlu_revisi: 'Perlu Revisi',
    artikel_diterima: 'Artikel Diterima',
    jadwal_ditetapkan: 'Jadwal Ditetapkan',
    siap_mengikuti_seminar: 'Siap Mengikuti Seminar',
    hadir: 'Hadir',
    selesai: 'Selesai',
    sertifikat_terbit: 'Sertifikat Terbit',
    ditolak: 'Ditolak',
};

export type StatusBadgeVariant = 'default' | 'secondary' | 'outline' | 'success' | 'warning' | 'destructive' | 'info';

export const registrationStatusVariants: Record<string, StatusBadgeVariant> = {
    draft: 'secondary',
    menunggu_pembayaran: 'warning',
    menunggu_verifikasi: 'warning',
    pembayaran_terverifikasi: 'info',
    artikel_diajukan: 'info',
    sedang_direview: 'info',
    perlu_revisi: 'warning',
    artikel_diterima: 'success',
    jadwal_ditetapkan: 'info',
    siap_mengikuti_seminar: 'info',
    hadir: 'success',
    selesai: 'success',
    sertifikat_terbit: 'success',
    ditolak: 'destructive',
};

export const articleStatusVariants: Record<string, StatusBadgeVariant> = {
    draft: 'secondary',
    diajukan: 'info',
    pemeriksaan_administrasi: 'info',
    perlu_perbaikan_administrasi: 'warning',
    ditolak_administrasi: 'destructive',
    proses_review: 'info',
    sedang_direview: 'info',
    revisi_minor: 'warning',
    revisi_mayor: 'warning',
    diterima: 'success',
    ditolak: 'destructive',
};

export const paymentStatusVariants: Record<string, StatusBadgeVariant> = {
    belum_bayar: 'secondary',
    menunggu_verifikasi: 'warning',
    terverifikasi: 'success',
    perlu_perbaikan: 'destructive',
};

export const attendanceStatusLabels: Record<string, string> = {
    belum_hadir: 'Belum Hadir',
    hadir: 'Hadir',
};

export const attendanceStatusVariants: Record<string, StatusBadgeVariant> = {
    belum_hadir: 'secondary',
    hadir: 'success',
};

export const reviewerAssignmentStatusLabels: Record<string, string> = {
    ditugaskan: 'Ditugaskan',
    sedang_direview: 'Sedang Direview',
    selesai: 'Selesai',
};

export const reviewerAssignmentStatusVariants: Record<string, StatusBadgeVariant> = {
    ditugaskan: 'warning',
    sedang_direview: 'info',
    selesai: 'success',
};

export const reviewRecommendationLabels: Record<string, string> = {
    diterima_tanpa_revisi: 'Diterima Tanpa Revisi',
    diterima_revisi_minor: 'Diterima dengan Revisi Minor',
    revisi_mayor: 'Revisi Mayor',
    ditolak: 'Ditolak',
};

export const reviewRecommendationVariants: Record<string, StatusBadgeVariant> = {
    diterima_tanpa_revisi: 'success',
    diterima_revisi_minor: 'success',
    revisi_mayor: 'warning',
    ditolak: 'destructive',
};

export const certificateRoleLabels: Record<string, string> = {
    peserta: 'Peserta',
    presenter: 'Presenter',
    moderator: 'Moderator',
    reviewer: 'Reviewer',
    narasumber: 'Narasumber',
    panitia: 'Panitia',
};

export const slotStatusLabels: Record<string, string> = {
    dijadwalkan: 'Dijadwalkan',
    hadir: 'Hadir',
    selesai: 'Selesai',
    tidak_hadir: 'Tidak Hadir',
    dijadwalkan_ulang: 'Dijadwalkan Ulang',
};

export const slotStatusVariants: Record<string, StatusBadgeVariant> = {
    dijadwalkan: 'info',
    hadir: 'success',
    selesai: 'success',
    tidak_hadir: 'destructive',
    dijadwalkan_ulang: 'warning',
};

export const publicationStatusLabels: Record<string, string> = {
    diproses: 'Diproses',
    terbit: 'Terbit',
};

export const publicationStatusVariants: Record<string, StatusBadgeVariant> = {
    diproses: 'warning',
    terbit: 'success',
};

export const announcementTypeLabels: Record<string, string> = {
    pengumuman: 'Pengumuman',
    dokumentasi: 'Dokumentasi',
    best_paper: 'Best Paper',
    best_presenter: 'Best Presenter',
    best_poster: 'Best Poster',
};

export const announcementTypeVariants: Record<string, StatusBadgeVariant> = {
    pengumuman: 'info',
    dokumentasi: 'secondary',
    best_paper: 'success',
    best_presenter: 'success',
    best_poster: 'success',
};

export function toChartData(record: Record<string, number>, labels: Record<string, string>): { label: string; value: number }[] {
    return Object.entries(record).map(([key, value]) => ({ label: labels[key] ?? key, value }));
}
