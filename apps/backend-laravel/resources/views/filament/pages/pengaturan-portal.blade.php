<x-filament-panels::page>
    <div style="display: grid; gap: 1.5rem;">
        <form wire:submit="save" style="display: grid; gap: 1.5rem;">
            <section style="display: grid; gap: 0.75rem; padding: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.4); border-radius: 0.75rem; background: white;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700;">Identitas Portal</h2>
                    <p style="color: #64748b;">Atur identitas portal publik dan informasi kontak yang tampil di landing page.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                    <label style="display: grid; gap: 0.35rem;">
                        <span>Judul Portal</span>
                        <input wire:model.live="portalTitle" type="text" placeholder="Portal Data Daerah Kabupaten Kepulauan Sangihe" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('portalTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Logo Portal</span>
                        <input type="file" wire:model="portalLogoUpload" accept=".jpg,.jpeg,.png,.webp,image/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @if ($this->portalLogoUrl)
                            <small style="color: #64748b;">Aktif: {{ $this->portalLogoPath }}</small>
                        @endif
                        @error('portalLogoUpload') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Deskripsi Footer</span>
                        <textarea wire:model.live="footerDescription" rows="3" placeholder="Deskripsi singkat portal untuk footer." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('footerDescription') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Alamat</span>
                        <input wire:model.live="contactAddress" type="text" placeholder="Alamat kantor / titik layanan" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('contactAddress') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Email</span>
                        <input wire:model.live="contactEmail" type="email" placeholder="admin@domain.go.id" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('contactEmail') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Telepon</span>
                        <input wire:model.live="contactPhone" type="text" placeholder="(0432) 21001" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('contactPhone') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                </div>
            </section>

            <section style="display: grid; gap: 0.75rem; padding: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.4); border-radius: 0.75rem; background: white;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700;">Hero Section</h2>
                    <p style="color: #64748b;">Hero dapat menggunakan gambar atau video. File tersimpan di storage/public/portal.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                    <label style="display: grid; gap: 0.35rem;">
                        <span>Tipe Background</span>
                        <select wire:model.live="heroBackgroundType" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            <option value="image">image</option>
                            <option value="video">video</option>
                        </select>
                        @error('heroBackgroundType') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    @if ($heroBackgroundType === 'image')
                        <label style="display: grid; gap: 0.35rem;">
                            <span>Upload Gambar Hero Utama</span>
                            <input type="file" wire:model="heroBackgroundImageUpload" accept=".jpg,.jpeg,.png,.webp,image/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            @if ($this->heroBackgroundImageUrl)
                                <small style="color: #64748b;">Aktif: {{ $this->heroBackgroundImagePath }}</small>
                            @endif
                            @error('heroBackgroundImageUpload') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                        </label>

                        <label style="display: grid; gap: 0.35rem;">
                            <span>Tambah Beberapa Gambar Hero</span>
                            <input type="file" wire:model="heroBackgroundImageUploads" multiple accept=".jpg,.jpeg,.png,.webp,image/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            <small style="color: #64748b;">Bisa pilih banyak foto sekaligus. Tidak berlaku untuk mode video.</small>
                            @error('heroBackgroundImageUploads') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                            @error('heroBackgroundImageUploads.*') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                        </label>

                        @if (count($this->heroBackgroundImageUrls))
                            <div style="grid-column: 1 / -1; display: grid; gap: 0.65rem;">
                                <span style="font-weight: 600;">Gambar Hero Aktif</span>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem;">
                                    @foreach ($this->heroBackgroundImageUrls as $index => $url)
                                        <div style="border: 1px solid #d1d5db; border-radius: 0.75rem; overflow: hidden; background: #f8fafc;">
                                            <div style="aspect-ratio: 16 / 9;">
                                                <img src="{{ $url }}" alt="Gambar hero {{ $index + 1 }}" style="width:100%; height:100%; object-fit:cover;">
                                            </div>
                                            <small style="display:block; padding: 0.45rem 0.6rem; color:#64748b;">#{{ $index + 1 }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <label style="display: grid; gap: 0.35rem;">
                            <span>Upload Video Hero</span>
                            <input type="file" wire:model="heroBackgroundVideoUpload" accept=".mp4,.webm,video/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            @if ($this->heroBackgroundVideoUrl)
                                <small style="color: #64748b;">Aktif: {{ $this->heroBackgroundVideoPath }}</small>
                            @endif
                            @error('heroBackgroundVideoUpload') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                        </label>

                        <label style="display: grid; gap: 0.35rem;">
                            <span>Poster Video</span>
                            <input type="file" wire:model="heroVideoPosterUpload" accept=".jpg,.jpeg,.png,.webp,image/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                            @if ($this->heroVideoPosterUrl)
                                <small style="color: #64748b;">Aktif: {{ $this->heroVideoPosterPath }}</small>
                            @endif
                            @error('heroVideoPosterUpload') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                        </label>
                    @endif

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Judul Hero</span>
                        <input wire:model.live="heroTitle" type="text" placeholder="Portal Data Daerah Kabupaten Kepulauan Sangihe" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Subjudul Hero</span>
                        <textarea wire:model.live="heroSubtitle" rows="3" placeholder="Ringkas dan kuat, maksimal 2-3 kalimat." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('heroSubtitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Badge Hero</span>
                        <input wire:model.live="heroBadgeText" type="text" placeholder="Satu Data Daerah" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroBadgeText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <div></div>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Utama</span>
                        <input wire:model.live="heroPrimaryButtonText" type="text" placeholder="Jelajahi Peta" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroPrimaryButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol Utama</span>
                        <input wire:model.live="heroPrimaryButtonLink" type="text" placeholder="/peta" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroPrimaryButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Kedua</span>
                        <input wire:model.live="heroSecondaryButtonText" type="text" placeholder="Lihat Statistik" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroSecondaryButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol Kedua</span>
                        <input wire:model.live="heroSecondaryButtonLink" type="text" placeholder="/statistik" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('heroSecondaryButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                </div>
            </section>

            <section style="display: grid; gap: 0.75rem; padding: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.4); border-radius: 0.75rem; background: white;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700;">Tentang Kabupaten</h2>
                    <p style="color: #64748b;">Konten singkat untuk halaman Tentang Daerah (publik). Gunakan teks ringkas dan mudah dipahami.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Judul</span>
                        <input wire:model.live="aboutRegionTitle" type="text" placeholder="Tentang Kabupaten Kepulauan Sangihe" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('aboutRegionTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Subjudul</span>
                        <textarea wire:model.live="aboutRegionSubtitle" rows="2" placeholder="Profil singkat daerah kepulauan..." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('aboutRegionSubtitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Konten</span>
                        <textarea wire:model.live="aboutRegionContent" rows="6" placeholder="Tulis konten tentang kabupaten..." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('aboutRegionContent') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Gambar</span>
                        <input type="file" wire:model="aboutRegionImageUpload" accept=".jpg,.jpeg,.png,.webp,image/*" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @if ($this->aboutRegionImageUrl)
                            <small style="color: #64748b;">Aktif: {{ $this->aboutRegionImagePath }}</small>
                        @endif
                        @error('aboutRegionImageUpload') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <div></div>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol</span>
                        <input wire:model.live="aboutRegionButtonText" type="text" placeholder="Tentang Daerah" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('aboutRegionButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol</span>
                        <input wire:model.live="aboutRegionButtonLink" type="text" placeholder="/tentang-daerah" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('aboutRegionButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                </div>
            </section>

            <section style="display: grid; gap: 0.75rem; padding: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.4); border-radius: 0.75rem; background: white;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700;">Informasi Landing Page</h2>
                    <p style="color: #64748b;">Atur teks ringkas untuk section peta, statistik, dan data terbuka di halaman beranda publik.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
                    <label style="display: grid; gap: 0.35rem;">
                        <span>Judul Peta Interaktif</span>
                        <input wire:model.live="mapHighlightTitle" type="text" placeholder="Peta Interaktif Daerah" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('mapHighlightTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Peta</span>
                        <input wire:model.live="mapHighlightButtonText" type="text" placeholder="Buka Peta Interaktif" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('mapHighlightButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Deskripsi Peta Interaktif</span>
                        <textarea wire:model.live="mapHighlightDescription" rows="2" placeholder="Klik kecamatan atau desa..." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('mapHighlightDescription') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol Peta</span>
                        <input wire:model.live="mapHighlightButtonLink" type="text" placeholder="/peta" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('mapHighlightButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <div></div>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Judul Statistik</span>
                        <input wire:model.live="statisticsHighlightTitle" type="text" placeholder="Statistik Pembangunan" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('statisticsHighlightTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Statistik</span>
                        <input wire:model.live="statisticsHighlightButtonText" type="text" placeholder="Lihat Semua Statistik" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('statisticsHighlightButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Deskripsi Statistik</span>
                        <textarea wire:model.live="statisticsHighlightDescription" rows="2" placeholder="Pantau indikator prioritas..." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('statisticsHighlightDescription') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol Statistik</span>
                        <input wire:model.live="statisticsHighlightButtonLink" type="text" placeholder="/statistik" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('statisticsHighlightButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <div></div>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Judul Data Terbuka</span>
                        <input wire:model.live="openDataTitle" type="text" placeholder="Data Terbuka untuk Masyarakat" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('openDataTitle') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Dataset</span>
                        <input wire:model.live="openDataPrimaryButtonText" type="text" placeholder="Lihat Dataset" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('openDataPrimaryButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem; grid-column: 1 / -1;">
                        <span>Deskripsi Data Terbuka</span>
                        <textarea wire:model.live="openDataDescription" rows="2" placeholder="Akses data agregat daerah..." style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;"></textarea>
                        @error('openDataDescription') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Link Tombol Dataset</span>
                        <input wire:model.live="openDataPrimaryButtonLink" type="text" placeholder="/data" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('openDataPrimaryButtonLink') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>

                    <label style="display: grid; gap: 0.35rem;">
                        <span>Teks Tombol Unduh</span>
                        <input wire:model.live="openDataSecondaryButtonText" type="text" placeholder="Unduh Data" style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem;">
                        @error('openDataSecondaryButtonText') <small style="color: #dc2626;">{{ $message }}</small> @enderror
                    </label>
                </div>
            </section>

            <section style="display: grid; gap: 0.75rem; padding: 1.25rem; border: 1px solid rgba(148, 163, 184, 0.4); border-radius: 0.75rem; background: white;">
                <div>
                    <h2 style="font-size: 1.125rem; font-weight: 700;">Preview</h2>
                    <p style="color: #64748b;">Preview kecil media hero (jika tersedia).</p>
                </div>

                <div style="border-radius: 0.75rem; overflow: hidden; border: 1px solid rgba(148, 163, 184, 0.35); background: #0b2545;">
                    <div style="position: relative; aspect-ratio: 16 / 6; min-height: 180px;">
                        @if ($heroBackgroundType === 'video' && $this->heroBackgroundVideoUrl)
                            <video autoplay muted loop playsinline preload="metadata" @if($this->heroVideoPosterUrl) poster="{{ $this->heroVideoPosterUrl }}" @endif style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
                                <source src="{{ $this->heroBackgroundVideoUrl }}" type="video/mp4">
                            </video>
                        @elseif ($this->heroBackgroundImageUrl)
                            <img src="{{ $this->heroBackgroundImageUrl }}" alt="Preview hero" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
                        @elseif ($this->heroVideoPosterUrl)
                            <img src="{{ $this->heroVideoPosterUrl }}" alt="Preview poster hero" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
                        @endif

                        <div style="position:absolute; inset:0; background: linear-gradient(to bottom, rgba(5, 20, 35, 0.75), rgba(5, 60, 90, 0.45));"></div>
                        <div style="position:absolute; inset:0; display:grid; place-items:center; padding: 1.25rem;">
                            <div style="max-width: 860px; text-align:center; color: white;">
                                <div style="display:inline-flex; align-items:center; gap:0.5rem; padding: 0.35rem 0.75rem; border-radius: 999px; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22);">
                                    <span style="font-size: 0.8rem;">{{ $heroBadgeText ?: 'Satu Data Daerah' }}</span>
                                </div>
                                <div style="margin-top: 0.75rem; font-weight: 800; font-size: 1.35rem;">
                                    {{ $heroTitle ?: 'Portal Data Daerah Kabupaten Kepulauan Sangihe' }}
                                </div>
                                <div style="margin-top: 0.5rem; color: rgba(255,255,255,0.85); font-size: 0.95rem;">
                                    {{ $heroSubtitle ?: 'Subjudul hero' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 0.9rem 1rem; display:flex; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; color: rgba(255,255,255,0.85);">
                        <small>Background: <strong style="color:white;">{{ $heroBackgroundType }}</strong></small>
                        <small>Gambar: <span style="color:white;">{{ $heroBackgroundImagePath ?: '-' }}</span></small>
                        <small>Video: <span style="color:white;">{{ $heroBackgroundVideoPath ?: '-' }}</span></small>
                        <small>Poster: <span style="color:white;">{{ $heroVideoPosterPath ?: '-' }}</span></small>
                    </div>
                </div>
            </section>

            <div style="display:flex; justify-content:flex-end; gap: 0.75rem;">
                <button type="submit" style="padding: 0.75rem 1rem; background: #0f4c81; color: white; border-radius: 0.6rem; border: none;">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
