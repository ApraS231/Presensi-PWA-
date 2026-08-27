@extends(Auth::user()->role === 'karyawan' ? 'layouts.pwa' : 'layouts.admin')

@section('title', 'Notifikasi - Presensi PT. CAK')
@section('page_title', 'Pemberitahuan Sistem')

@section('content')
<div style="display: flex; flex-direction: column; gap: 16px;">

    <!-- Top Action Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: var(--md-sys-color-on-surface); margin: 0;">Pemberitahuan</h2>
            <div style="font-size: 12.5px; color: var(--md-sys-color-on-surface-variant); margin-top: 2px;">
                {{ $unreadCount }} notifikasi belum dibaca
            </div>
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="md-btn-tonal" style="height: 36px; padding: 0 16px; font-size: 13px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">done_all</span>
                    <span>Tandai Semua Terbaca</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Notification List Container -->
    @if($notifications->isEmpty())
        <div class="md-card-filled" style="text-align: center; padding: 48px 24px;">
            <div style="width: 56px; height: 56px; border-radius: var(--md-sys-shape-corner-full); background: var(--md-sys-color-surface-container-highest); color: var(--md-sys-color-outline); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <span class="material-symbols-rounded" style="font-size: 28px;">notifications_off</span>
            </div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--md-sys-color-on-surface); margin-bottom: 4px;">Tidak Ada Notifikasi</h3>
            <p style="font-size: 13.5px; color: var(--md-sys-color-on-surface-variant); margin: 0;">
                Semua pemberitahuan dan pengumuman sistem akan ditampilkan di sini.
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($notifications as $notif)
                @php
                    $iconName = match($notif->type) {
                        'leave_approved'       => 'check_circle',
                        'leave_rejected'       => 'cancel',
                        'reminder_presensi'    => 'alarm',
                        'enrollment_required'  => 'face',
                        'system_announcement'  => 'campaign',
                        default                => 'notifications'
                    };

                    $iconColorClass = match($notif->type) {
                        'leave_approved'       => 'color: var(--md-custom-color-success); background: var(--md-custom-color-success-container);',
                        'leave_rejected'       => 'color: var(--md-sys-color-error); background: var(--md-sys-color-error-container);',
                        'reminder_presensi'    => 'color: var(--md-custom-color-warning); background: var(--md-custom-color-warning-container);',
                        'enrollment_required'  => 'color: var(--md-sys-color-primary); background: var(--md-sys-color-primary-container);',
                        'system_announcement'  => 'color: var(--md-sys-color-tertiary); background: var(--md-sys-color-tertiary-container);',
                        default                => 'color: var(--md-sys-color-secondary); background: var(--md-sys-color-secondary-container);'
                    };
                @endphp

                <div class="{{ $notif->is_read ? 'md-card-outlined' : 'md-card-elevated' }}" 
                     style="display: flex; align-items: flex-start; gap: 14px; padding: 16px; position: relative; {{ !$notif->is_read ? 'border-left: 4px solid var(--md-sys-color-primary);' : '' }}">
                    
                    <!-- Icon Avatar -->
                    <div style="width: 40px; height: 40px; border-radius: var(--md-sys-shape-corner-medium); display: flex; align-items: center; justify-content: center; flex-shrink: 0; {{ $iconColorClass }}">
                        <span class="material-symbols-rounded" style="font-size: 20px;">{{ $iconName }}</span>
                    </div>

                    <!-- Content -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <h4 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--md-sys-color-on-surface);">
                                {{ $notif->title }}
                            </h4>
                            <span style="font-size: 11px; color: var(--md-sys-color-outline); white-space: nowrap;">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p style="font-size: 13px; color: var(--md-sys-color-on-surface-variant); margin: 6px 0 0 0; line-height: 1.45;">
                            {{ $notif->message }}
                        </p>

                        @if(!$notif->is_read)
                            <div style="margin-top: 10px;">
                                <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="background: none; border: none; padding: 0; font-size: 12px; font-weight: 600; color: var(--md-sys-color-primary); cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 16px;">done</span>
                                        Tandai dibaca
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 16px;">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    @endif

</div>
@endsection
