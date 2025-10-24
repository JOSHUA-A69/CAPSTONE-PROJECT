<footer class="bg-[#2ecc71] text-white mt-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 text-sm lg:text-base">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 items-start">

            <!-- Left: Logo + description -->
            <div class="flex items-start gap-4">
                <img src="/images/ers-logo.png" alt="eReligiousServices" class="w-12 h-12 rounded-full object-cover" />
                <div>
                    <div class="text-xl font-bold">eReligiousServices</div>
                    <div class="text-sm opacity-90">HNU CREaM</div>
                    <p class="mt-3 text-base text-white/95 max-w-xs leading-relaxed">The Center for Religious Education and Mission serves as the hub of Catholic and Christian formation at Holy Name University, guided by the four pillars of L.O.V.E.</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-base font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-base text-white/95">
                    <li><a href="#" class="hover:underline">Home</a></li>
                    <li><a href="{{ route('login') }}" class="hover:underline">Sign In</a></li>
                    <li><a href="{{ route('register') }}" class="hover:underline">Register</a></li>
                </ul>
            </div>

            <!-- Our Services -->
            <div>
                <h4 class="text-base font-semibold mb-3">Our Services</h4>
                <ul class="space-y-2 text-base text-white/95">
                    <li><a href="#" class="hover:underline">Liturgical Celebrations</a></li>
                    <li><a href="#" class="hover:underline">Retreats & Recollections</a></li>
                    <li><a href="#" class="hover:underline">Prayer Services</a></li>
                    <li><a href="#" class="hover:underline">Outreach Activities</a></li>
                    <li><a href="#" class="hover:underline">Daily Noon Mass</a></li>
                    <li><a href="#" class="hover:underline">Catechetical Activities</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-base font-semibold mb-3">Contact CREaM</h4>
                <ul class="text-base text-white/95 space-y-2">
                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-1 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg><span>Holy Name University<br/>Tagbilaran City, Bohol<br/>Philippines</span></li>
                    <!-- Phone -->
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 3.25h2A1.25 1.25 0 016.75 4.5v2a1.25 1.25 0 01-.37.89l-1.1 1.1a14.5 14.5 0 006.98 6.98l1.1-1.1c.24-.24.56-.37.9-.37h2a1.25 1.25 0 011.25 1.25v2A1.25 1.25 0 0116.25 19h-1.5C8.6 19 5 15.4 5 10.25V8.75A1.25 1.25 0 013.75 7.5h-.25A1.25 1.25 0 012.25 6.25v-2c0-.55.45-1 1-1z"/></svg>+63 (38) 411-3715</li>
                    <li class="flex items-center gap-2"><svg class="w-4 h-4 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5v7A2.5 2.5 0 0 0 5.5 18h13a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 18.5 6h-13A2.5 2.5 0 0 0 3 8.5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5L12 13 3 8.5"></path></svg><a href="mailto:cream@hnu.edu.ph" class="hover:underline">cream@hnu.edu.ph</a></li>
                    <!-- Office Hours -->
                    <li class="flex items-start gap-2"><svg class="w-4 h-4 mt-1 text-white/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6a1 1 0 001 1h3"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span>Mon - Fri: 8:00 AM - 5:00 PM<br/>Mass: 12:00 PM</span></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/20 mt-6 pt-4">
            <div class="flex items-center justify-between">
                <div class="text-sm lg:text-base">© {{ date('Y') }} Holy Name University - Center for Religious Education and Mission. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <a href="https://www.facebook.com/profile.php?id=100080138541118" target="_blank" rel="noopener" class="text-white/90 hover:text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07C2 17.09 5.66 21.24 10.44 21.95v-6.96H7.9v-2.99h2.54V9.83c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.99h-2.34v6.96C18.34 21.24 22 17.09 22 12.07z"/></svg>
                    </a>
                    <a href="mailto:cream@hnu.edu.ph" class="text-white/90 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8.5v7A2.5 2.5 0 0 0 5.5 18h13a2.5 2.5 0 0 0 2.5-2.5v-7A2.5 2.5 0 0 0 18.5 6h-13A2.5 2.5 0 0 0 3 8.5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.5L12 13 3 8.5"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
