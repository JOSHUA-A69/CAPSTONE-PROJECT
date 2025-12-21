<footer class="bg-[#2ecc71] dark:bg-dark-bg text-white mt-16 no-animations relative overflow-hidden">
    <!-- Decorative wave top -->
    <div class="absolute top-0 left-0 right-0 h-4 bg-gradient-to-b from-transparent to-[#2ecc71] dark:to-dark-bg opacity-50"></div>
    
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 text-sm lg:text-base relative">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 items-start">

            <!-- Left: Logo + description -->
            <div class="flex items-start gap-4 group">
                <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-14 h-14 rounded-full object-cover shadow-lg group-hover:scale-110 transition-transform duration-300" />
                <div>
                    <div class="text-xl font-bold tracking-wide">eReligiousServices</div>
                    <div class="text-sm opacity-90 dark:text-dark-muted">HNU CREaM</div>
                    <p class="mt-3 text-base text-white/95 dark:text-dark-muted max-w-xs leading-relaxed">The Center for Religious Education and Mission serves as the hub of Catholic and Christian formation at Holy Name University, guided by the four pillars of L.O.V.E.</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="w-8 h-0.5 bg-white/50 rounded"></span>
                    Quick Links
                </h4>
                <ul class="space-y-3 text-base text-white/95 dark:text-dark-muted">
                    <li><a href="#" class="inline-flex items-center gap-2 transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-2 hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">
                        <svg class="w-4 h-4 opacity-0 -ml-6 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Home
                    </a></li>
                    <li><a href="{{ route('login') }}" class="inline-flex items-center gap-2 transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-2 hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">
                        <svg class="w-4 h-4 opacity-0 -ml-6 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Sign In
                    </a></li>
                    <li><a href="{{ route('register') }}" class="inline-flex items-center gap-2 transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-2 hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">
                        <svg class="w-4 h-4 opacity-0 -ml-6 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Register
                    </a></li>
                </ul>
            </div>

            <!-- Our Services -->
            <div>
                <h4 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="w-8 h-0.5 bg-white/50 rounded"></span>
                    Our Services
                </h4>
                <ul class="space-y-3 text-base text-white/95 dark:text-dark-muted">
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Liturgical Celebrations</a></li>
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Retreats & Recollections</a></li>
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Prayer Services</a></li>
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Outreach Activities</a></li>
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Daily Noon Mass</a></li>
                    <li><a href="#" class="transition-all duration-300 hover:text-white dark:hover:text-emerald-300 hover:translate-x-1 inline-block hover:underline underline-offset-4 decoration-emerald-200/80 dark:decoration-emerald-400/70">Catechetical Activities</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-base font-bold mb-4 flex items-center gap-2">
                    <span class="w-8 h-0.5 bg-white/50 rounded"></span>
                    Contact CREaM
                </h4>
                <ul class="text-base text-white/95 dark:text-dark-muted space-y-3">
                    <li class="flex items-start gap-3 group">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4 text-white/90 dark:text-dark-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                        </div>
                        <span>Holy Name University<br/>Tagbilaran City, Bohol<br/>Philippines</span>
                    </li>
                    <li class="flex items-center gap-3 group">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4 text-white/90 dark:text-dark-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        +63 (38) 411-3715
                    </li>
                    <li class="flex items-center gap-3 group">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4 text-white/90 dark:text-dark-muted" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5v7A2.5 2.5 0 0 0 5.5 18h13a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 18.5 6h-13A2.5 2.5 0 0 0 3 8.5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5L12 13 3 8.5"></path></svg>
                        </div>
                        <a href="mailto:cream@hnu.edu.ph" class="transition-colors duration-200 hover:text-white dark:hover:text-emerald-300 hover:underline underline-offset-2 decoration-emerald-200/80 dark:decoration-emerald-400/70">cream@hnu.edu.ph</a>
                    </li>
                    <li class="flex items-start gap-3 group">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6a1 1 0 001 1h3"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span>Mon - Fri: 8:00 AM - 5:00 PM<br/>Mass: 12:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/20 dark:border-dark-border mt-10 pt-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm lg:text-base dark:text-dark-muted text-center sm:text-left">© {{ date('Y') }} Holy Name University - Center for Religious Education and Mission. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <a href="https://www.facebook.com/profile.php?id=100080138541118" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center text-white/90 dark:text-dark-muted transition-all duration-300 hover:bg-white/20 hover:text-white dark:hover:text-emerald-300 hover:scale-110 hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07C2 17.09 5.66 21.24 10.44 21.95v-6.96H7.9v-2.99h2.54V9.83c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.99h-2.34v6.96C18.34 21.24 22 17.09 22 12.07z"/></svg>
                    </a>
                    <a href="mailto:cream@hnu.edu.ph" class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center text-white/90 dark:text-dark-muted transition-all duration-300 hover:bg-white/20 hover:text-white dark:hover:text-emerald-300 hover:scale-110 hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5v7A2.5 2.5 0 0 0 5.5 18h13a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 18.5 6h-13A2.5 2.5 0 0 0 3 8.5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5L12 13 3 8.5"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
