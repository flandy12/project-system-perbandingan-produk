<style>
.matrix-footer{background:#f8fafc;border-top:1px solid #e2e8f0;color:#475569;margin-top:48px;font-size:14px}
.matrix-footer__inner{max-width:1280px;margin:auto;padding:48px 32px 24px}
.matrix-footer__grid{display:grid;grid-template-columns:1.4fr .7fr 1.1fr;gap:64px}
.matrix-footer__logo{height:36px;width:auto;margin-bottom:20px}
.matrix-footer h3{color:#0f172a;font-size:14px;font-weight:700;margin:0 0 18px}
.matrix-footer p{margin:0;line-height:1.8}
.matrix-footer__name{font-weight:600;color:#334155;margin-bottom:8px!important}
.matrix-footer__address{max-width:350px;font-size:13px}
.matrix-footer a{transition:color .18s,background .18s,transform .18s;text-decoration:none}
.matrix-footer a:hover{color:#0284c7}
.matrix-footer a:focus-visible{outline:2px solid #0284c7;outline-offset:4px}
.matrix-footer__links{display:grid;gap:12px;list-style:none;margin:0;padding:0}
.matrix-footer__contact{display:grid;gap:14px}
.matrix-footer__contact a{display:flex;align-items:center;gap:10px;overflow-wrap:anywhere}
.matrix-footer__contact svg{width:18px;height:18px;flex-shrink:0;color:#64748b}
.matrix-footer__social{display:flex;gap:10px;margin-top:22px}
.matrix-footer__social a{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border:1px solid #e2e8f0;background:#fff;border-radius:10px;color:#475569}
.matrix-footer__social a:hover{background:#e0f2fe;color:#0284c7;transform:translateY(-2px)}
.matrix-footer__bottom{border-top:1px solid #e2e8f0;margin-top:36px;padding-top:20px;display:flex;justify-content:space-between;gap:16px;color:#64748b;font-size:12px}
@media(max-width:768px){.matrix-footer__inner{padding:32px 24px 20px}.matrix-footer__grid{grid-template-columns:1fr 1fr;gap:32px}.matrix-footer__brand{grid-column:1/-1}.matrix-footer__bottom{flex-direction:column;margin-top:28px}}
@media(max-width:440px){.matrix-footer__grid{grid-template-columns:1fr}.matrix-footer__brand{grid-column:auto}}
</style>
<footer class="matrix-footer" aria-label="Informasi perusahaan">
    <div class="matrix-footer__inner">
        <div class="matrix-footer__grid">
            <div class="matrix-footer__brand">
                <a href="{{ config('company.website') }}" target="_blank" rel="noopener noreferrer"><img src="{{ asset('images/logo.png') }}" alt="Matrix Parabola" class="matrix-footer__logo"></a>
                <p class="matrix-footer__name">{{ config('company.name') }}</p>
                <p class="matrix-footer__address">{{ config('company.address') }}</p>
            </div>
            <nav aria-label="Navigasi footer">
                <h3>Jelajahi</h3>
                <ul class="matrix-footer__links">
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ url('/gallery') }}">Katalog produk</a></li>
                    <li><a href="{{ url('/compare') }}">Bandingkan produk</a></li>
                    <li><a href="https://www.matrixparabola.com/service-center" target="_blank" rel="noopener noreferrer">Service Center</a></li>
                </ul>
            </nav>
            <div>
                <h3>Hubungi kami</h3>
                <div class="matrix-footer__contact">
                    <a href="{{ config('company.whatsapp') }}" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M6 3H3v4c0 7.7 6.3 14 14 14h4v-3l-5-3-2 2a14 14 0 0 1-7-7l2-2-3-5Z"/></svg>{{ config('company.phone') }} <span style="font-size:11px;color:#64748b">WhatsApp</span></a>
                    <a href="mailto:{{ config('company.email') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg>{{ config('company.email') }}</a>
                    <a href="{{ config('company.website') }}" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/></svg>matrixparabola.com</a>
                </div>
                <div class="matrix-footer__social" aria-label="Sosial media Matrix">
                    @foreach(['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube'] as $name => $label)
                        <a href="{{ config('company.'.$name) }}" aria-label="{{ $label }} Matrix" title="{{ $label }} Matrix" target="_blank" rel="noopener noreferrer"><x-social-icon :name="$name" /></a>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="matrix-footer__bottom">
            <span>© {{ now()->year }} {{ config('company.name') }}.</span>
            <span>Informasi produk dan perbandingan STB</span>
        </div>
    </div>
</footer>
