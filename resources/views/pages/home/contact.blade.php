<x-frontend-layout>

    <section class="bg-gray-50 py-16 px-4">
        <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12">

            <!-- LEFT INFO -->
            <div>
                <h2 class="text-3xl font-bold mb-4">Hubungi Kami</h2>
                <p class="text-gray-600 mb-6">
                    Punya pertanyaan, kerja sama, atau butuh bantuan?
                    Tim kami siap membantu Anda.
                </p>

                <div class="space-y-4 text-sm">
                    <div>
                        <p class="font-semibold">Email</p>
                        <p class="text-gray-600">support@email.com</p>
                    </div>

                    <div>
                        <p class="font-semibold">Telepon</p>
                        <p class="text-gray-600">+62 821-XXXX-XXXX</p>
                    </div>

                    <div>
                        <p class="font-semibold">Alamat</p>
                        <p class="text-gray-600">
                            Jakarta, Indonesia
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <div class="bg-white p-8 rounded-2xl shadow-md">
                <form method="POST" action="/contact">
                    @csrf

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <input type="text" name="name" placeholder="Nama"
                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-sky-400 outline-none">

                        <input type="email" name="email" placeholder="Email"
                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-sky-400 outline-none">
                    </div>

                    <textarea name="message" rows="5" placeholder="Pesan"
                        class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-sky-400 outline-none mb-4"></textarea>

                    <button type="submit"
                        class="w-full bg-sky-600 text-white py-3 rounded-lg hover:bg-sky-700 transition">
                        Kirim Pesan
                    </button>
                </form>
            </div>

        </div>
    </section>

</x-frontend-layout>
