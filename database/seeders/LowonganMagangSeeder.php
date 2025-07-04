<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LowonganMagangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_lowongan_magang')->delete();
        DB::statement('ALTER TABLE t_lowongan_magang AUTO_INCREMENT = 1');

        $periodeList = DB::table('m_periode')
            ->whereIn('nama_periode', ['2024/2025 Ganjil', '2024/2025 Genap', '2025/2026 Ganjil'])
            ->get();

        // Ambil data perusahaan lengkap dengan nama
        $perusahaanList = DB::table('m_perusahaan')->get();
        if ($perusahaanList->isEmpty()) {
            throw new \Exception('Tabel m_perusahaan kosong. Harap isi terlebih dahulu.');
        }

        $waktuMagang = DB::table('m_waktu_magang')->pluck('id_waktu_magang')->toArray();
        $daerah = DB::table('m_daerah_magang')->pluck('id_daerah_magang')->toArray();
        $jenisMagang = DB::table('m_jenis_magang')->pluck('id_jenis_magang')->toArray();
        $insentif = DB::table('m_insentif')->pluck('id_insentif')->toArray();
        
        // Daftar judul dan deskripsi lowongan profesional 
        $lowonganTemplates = [
            [
                'judul' => 'Magang Web Developer',
                'deskripsi' => '<p><strong>🚀 POSISI WEB DEVELOPER INTERN 🚀</strong></p>
        <p>Kami mencari mahasiswa berbakat untuk mengembangkan aplikasi web inovatif menggunakan teknologi modern seperti <strong>Laravel</strong>, <strong>React</strong>, atau <strong>Vue.js</strong>.</p>

        <p><strong>Apa yang akan kamu lakukan:</strong></p>
        <p>• Mengembangkan fitur-fitur baru & menarik<br>
        • Implementasi UI/UX dari desainer<br>
        • Integrasi dengan berbagai API<br>
        • Berkolaborasi dalam tim development</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Mahasiswa semester 6-8 jurusan <em>Teknik Informatika/Sistem Informasi</em><br>
        • Memiliki pemahaman dasar HTML, CSS, dan JavaScript<br>
        • Kemampuan problem-solving yang baik</p>

        <p><em>Bergabunglah dengan kami untuk pengalaman magang yang berharga!</em></p>'
            ],
            [
                'judul' => 'Magang Mobile Developer',
                'deskripsi' => '<p><strong>📱 MOBILE DEVELOPER INTERNSHIP PROGRAM 📱</strong></p>
        <p>Kesempatan untuk mengembangkan aplikasi mobile yang akan digunakan oleh ribuan pengguna! Pelajari pengembangan Android/iOS <em>from scratch</em> dengan bimbingan developer berpengalaman.</p>

        <p><strong>Tanggung Jawab:</strong></p>
        <p>• Mengembangkan aplikasi mobile dari konsep hingga implementasi<br>
        • Membuat UI yang responsive dan user-friendly<br>
        • Integrasi dengan backend services<br>
        • Unit testing dan bug fixing</p>

        <p><strong>Yang Kami Cari:</strong></p>
        <p>• Pemahaman dasar <strong>Java/Kotlin/Swift</strong><br>
        • Pengalaman dengan <em>Android Studio/Xcode</em><br>
        • Kemampuan pemrograman berorientasi objek</p>

        <p><em>Jadilah bagian dari revolusi mobile bersama kami!</em></p>'
            ],
            [
                'judul' => 'Magang UI/UX Designer',
                'deskripsi' => '<p><strong>🎨 UI/UX DESIGN INTERNSHIP 🎨</strong></p>
        <p>Ciptakan pengalaman pengguna yang <em>menakjubkan</em> dan antarmuka yang <em>memukau</em>! Magang di posisi UI/UX Designer membuka kesempatan untuk mengasah kreativitasmu.</p>

        <p><strong>Aktivitas Magang:</strong></p>
        <p>• User research dan persona development<br>
        • Wireframing & prototyping<br>
        • User testing & iterasi desain<br>
        • Berkolaborasi dengan tim development</p>

        <p><strong>Persyaratan:</strong></p>
        <p>• Portofolio desain (walaupun berupa tugas kuliah)<br>
        • Familiar dengan <strong>Figma</strong>, <strong>Adobe XD</strong>, atau <strong>Sketch</strong><br>
        • Pemahaman dasar tentang prinsip desain dan UX</p>

        <p><em>Turn your design passion into real-world experience!</em></p>'
            ],
            [
                'judul' => 'Magang Data Analyst',
                'deskripsi' => '<p><strong>📊 DATA ANALYST INTERNSHIP OPPORTUNITY 📊</strong></p>
        <p>Temukan insight berharga dari kumpulan data besar! Pelajari cara menganalisis dan memvisualisasikan data untuk mendukung keputusan bisnis strategis.</p>

        <p><strong>Scope Pekerjaan:</strong></p>
        <p>• Mengolah dan membersihkan dataset kompleks<br>
        • Melakukan analisis statistik dengan <strong>Python</strong> dan <strong>SQL</strong><br>
        • Membuat visualisasi data yang informatif<br>
        • Menyusun laporan insight untuk stakeholders</p>

        <p><strong>Kriteria Ideal:</strong></p>
        <p>• Mahasiswa jurusan <em>Statistik, Matematika, atau Ilmu Komputer</em><br>
        • Kemampuan dasar SQL dan Python<br>
        • Familiar dengan tools seperti Pandas, Tableau, atau Power BI</p>

        <p><em>Buka pintu karier data science melalui magang ini!</em></p>'
            ],
            [
                'judul' => 'Magang Network Engineer',
                'deskripsi' => '<p><strong>🌐 NETWORK ENGINEER INTERNSHIP 🌐</strong></p>
        <p>Pelajari cara mengelola infrastruktur jaringan enterprise secara langsung! Program magang ini memberikanmu pengalaman hands-on dengan teknologi jaringan terkini.</p>

        <p><strong>Yang Akan Kamu Pelajari:</strong></p>
        <p>• Konfigurasi dan manajemen <strong>router, switch, dan firewall</strong><br>
        • Network monitoring dan troubleshooting<br>
        • Implementasi security measures<br>
        • Dokumentasi infrastruktur jaringan</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>TCP/IP dan OSI Layer</em><br>
        • Familiar dengan konsep jaringan komputer<br>
        • Minat kuat di bidang networking</p>

        <p><em>Build the backbone of digital infrastructure!</em></p>'
            ],
            [
                'judul' => 'Magang Cyber Security',
                'deskripsi' => '<p><strong>🔒 CYBER SECURITY INTERN WANTED 🔒</strong></p>
        <p>Lindungi aset digital dari ancaman cyber! Magang di bidang keamanan siber akan membekalimu dengan skill yang sangat dicari di era digital ini.</p>

        <p><strong>Tanggung Jawab:</strong></p>
        <p>• Membantu melakukan <strong>security assessments</strong> dan penetration testing<br>
        • Monitoring keamanan sistem dan jaringan<br>
        • Analisis log dan investigasi potensi insiden<br>
        • Implementasi best practices keamanan</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Minat kuat di bidang <em>cyber security</em><br>
        • Pemahaman dasar tentang kriptografi dan keamanan jaringan<br>
        • Familiar dengan konsep vulnerability dan threats</p>

        <p><em>Jadilah garda terdepan dalam pertahanan cyber!</em></p>'
            ],
            [
                'judul' => 'Magang DevOps Engineer',
                'deskripsi' => '<p><strong>⚙️ DEVOPS ENGINEERING INTERNSHIP ⚙️</strong></p>
        <p>Otomatisasi, integrasi, dan delivery! Pelajari cara menjembatani development dan operations untuk delivery software yang lebih cepat dan andal.</p>

        <p><strong>Lingkup Magang:</strong></p>
        <p>• Implementasi pipeline <strong>CI/CD</strong> (Jenkins, GitLab CI)<br>
        • Konfigurasi dan manajemen container (Docker, Kubernetes)<br>
        • Infrastructure as Code dengan Terraform/Ansible<br>
        • Monitoring dan logging sistem</p>

        <p><strong>Persyaratan:</strong></p>
        <p>• Familiar dengan <em>sistem operasi Linux</em><br>
        • Dasar-dasar scripting (Bash, Python)<br>
        • Pemahaman konsep cloud computing</p>

        <p><em>Automate everything dalam magang DevOps ini!</em></p>'
            ],
            [
                'judul' => 'Magang QA Engineer',
                'deskripsi' => '<p><strong>🐞 QUALITY ASSURANCE INTERNSHIP 🐞</strong></p>
        <p>Memastikan software bebas bug dan memenuhi standar kualitas! Magang sebagai QA Engineer akan mengajarkanmu keterampilan testing yang komprehensif.</p>

        <p><strong>Aktivitas Magang:</strong></p>
        <p>• Melakukan <strong>manual testing</strong> untuk fitur baru<br>
        • Implementasi automated testing (Selenium, Cypress)<br>
        • Bug tracking dan pelaporan<br>
        • Menciptakan test cases dan test plans</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>SDLC</em><br>
        • Detail-oriented dan analytical thinking<br>
        • Kemampuan komunikasi yang baik untuk melaporkan issues</p>

        <p><em>Break it before users do!</em></p>'
            ],
            [
                'judul' => 'Magang Frontend Developer',
                'deskripsi' => '<p><strong>🖥️ FRONTEND DEVELOPER INTERNSHIP 🖥️</strong></p>
        <p>Ciptakan antarmuka web yang responsif, interaktif, dan menarik! Asah skill front-end development-mu dengan teknologi terkini.</p>

        <p><strong>Yang Akan Kamu Kerjakan:</strong></p>
        <p>• Implementasi UI dari desain dengan <strong>HTML5, CSS3, dan JavaScript</strong><br>
        • Pengembangan aplikasi dengan React/Vue/Angular<br>
        • Optimasi performa frontend<br>
        • Adaptasi UI untuk berbagai ukuran layar (responsive design)</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Portofolio proyek web (personal/akademik)<br>
        • Pemahaman tentang <em>CSS frameworks</em> (Bootstrap, Tailwind)<br>
        • Basic knowledge tentang UX/UI principles</p>

        <p><em>Bring designs to life with code!</em></p>'
            ],
            [
                'judul' => 'Magang Backend Developer',
                'deskripsi' => '<p><strong>⚡ BACKEND DEVELOPER INTERNSHIP ⚡</strong></p>
        <p>Bangun fondasi yang kuat untuk aplikasi modern! Magang Backend Developer akan membekalimu dengan keterampilan pengembangan server, database, dan API.</p>

        <p><strong>Tanggung Jawab:</strong></p>
        <p>• Pengembangan API dengan <strong>PHP/Python/Node.js</strong><br>
        • Database design dan query optimization<br>
        • Implementasi business logic dan authorization<br>
        • Integrasi dengan third-party services</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>REST API</em><br>
        • Familiar dengan database SQL/NoSQL<br>
        • Pengetahuan tentang arsitektur aplikasi</p>

        <p><em>Power the logic behind great applications!</em></p>'
            ],
            [
                'judul' => 'Magang Full Stack Developer',
                'deskripsi' => '<p><strong>🔄 FULL STACK DEVELOPER INTERNSHIP 🔄</strong></p>
        <p>Kuasai frontend dan backend development sekaligus! Program magang ini menawarkan pengalaman komprehensif dalam pengembangan aplikasi end-to-end.</p>

        <p><strong>Scope Pekerjaan:</strong></p>
        <p>• Pengembangan fitur <strong>end-to-end</strong> dari database hingga UI<br>
        • Implementasi front-end dengan HTML/CSS/JavaScript frameworks<br>
        • Pengembangan backend dan API services<br>
        • Integrasi dan testing komponen sistem</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pengetahuan dasar <em>HTML, CSS, JavaScript</em><br>
        • Familiar dengan minimal satu bahasa backend (PHP, Python, Node.js)<br>
        • Pemahaman tentang database dan RESTful APIs</p>

        <p><em>Bangun aplikasi dari nol hingga production!</em></p>'
            ],
            [
                'judul' => 'Magang Business Intelligence',
                'deskripsi' => '<p><strong>📈 BUSINESS INTELLIGENCE INTERNSHIP 📈</strong></p>
        <p>Ubah data menjadi keputusan bisnis! Magang di bidang Business Intelligence akan mengajarkanmu cara menganalisis data untuk mendukung strategi perusahaan.</p>

        <p><strong>Aktivitas Magang:</strong></p>
        <p>• Mengembangkan <strong>dashboard interaktif</strong> dengan Power BI/Tableau/Looker<br>
        • ETL (Extract, Transform, Load) data dari berbagai sumber<br>
        • Analisis trend dan pattern dalam data bisnis<br>
        • Presentasi insight kepada stakeholders</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>database dan SQL</em><br>
        • Kemampuan analitis dan statistik<br>
        • Familiar dengan konsep visualisasi data</p>

        <p><em>Turn complex data into clear business insights!</em></p>'
            ],
            [
                'judul' => 'Magang Database Administrator',
                'deskripsi' => '<p><strong>🗄️ DATABASE ADMINISTRATOR INTERNSHIP 🗄️</strong></p>
        <p>Kelola jantung dari sistem informasi! Program magang DBA ini akan mengajarkanmu cara mendesain, mengoptimalkan, dan memelihara database perusahaan.</p>

        <p><strong>Yang Akan Kamu Pelajari:</strong></p>
        <p>• Manajemen <strong>database SQL dan NoSQL</strong><br>
        • Query optimization dan performance tuning<br>
        • Backup, recovery, dan disaster planning<br>
        • Database security dan access control</p>

        <p><strong>Persyaratan:</strong></p>
        <p>• Pemahaman dasar tentang <em>SQL</em> dan relational databases<br>
        • Familiar dengan jenis-jenis database (MySQL, PostgreSQL, MongoDB)<br>
        • Kemampuan analitis untuk troubleshooting</p>

        <p><em>Manage the data that powers modern businesses!</em></p>'
            ],
            [
                'judul' => 'Magang Machine Learning',
                'deskripsi' => '<p><strong>🤖 MACHINE LEARNING INTERNSHIP 🤖</strong></p>
        <p>Kembangkan model prediktif dan algoritma cerdas! Magang di bidang Machine Learning menawarkan kesempatan untuk menerapkan AI dalam kasus nyata.</p>

        <p><strong>Lingkup Magang:</strong></p>
        <p>• Pengembangan model <strong>machine learning</strong> dengan Python<br>
        • Data preprocessing dan feature engineering<br>
        • Model evaluation dan hyperparameter tuning<br>
        • Deployment model ke production environment</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Latar belakang <em>matematika/statistik</em><br>
        • Pemahaman dasar tentang algoritma ML<br>
        • Familiar dengan Python dan libraries ML (scikit-learn, TensorFlow, PyTorch)</p>

        <p><em>Build the AI-powered future with us!</em></p>'
            ],
            [
                'judul' => 'Magang IT Support',
                'deskripsi' => '<p><strong>🛠️ IT SUPPORT INTERNSHIP 🛠️</strong></p>
        <p>Jadi pahlawan teknologi bagi pengguna! Magang IT Support memberikanmu pengalaman praktis dalam menyelesaikan berbagai masalah teknis.</p>

        <p><strong>Tanggung Jawab:</strong></p>
        <p>• <strong>Troubleshooting</strong> hardware dan software<br>
        • Setup dan konfigurasi perangkat baru<br>
        • Manajemen user accounts dan akses<br>
        • Dokumentasi IT dan knowledge base</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>sistem operasi</em> (Windows, MacOS, Linux)<br>
        • Kemampuan komunikasi yang baik untuk bantuan pengguna<br>
        • Problem-solving mindset</p>

        <p><em>Be the tech hero everyone needs!</em></p>'
            ],
            [
                'judul' => 'Magang AR/VR Developer',
                'deskripsi' => '<p><strong>🥽 AR/VR DEVELOPER INTERNSHIP 🥽</strong></p>
        <p>Ciptakan dunia virtual yang menakjubkan! Magang sebagai AR/VR Developer memberimu kesempatan untuk bereksperimen dengan teknologi immersive.</p>

        <p><strong>Yang Akan Kamu Kerjakan:</strong></p>
        <p>• Pengembangan aplikasi <strong>Augmented Reality dan Virtual Reality</strong><br>
        • Implementasi interaksi user dalam lingkungan 3D<br>
        • Optimasi performa untuk perangkat mobile/headset<br>
        • Testing experience AR/VR pada berbagai device</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>3D modeling</em> dan game development<br>
        • Familiar dengan Unity, ARKit/ARCore, atau platform serupa<br>
        • Kreativitas dan spatial thinking</p>

        <p><em>Shape the future of immersive experiences!</em></p>'
            ],
            [
                'judul' => 'Magang Cloud Engineer',
                'deskripsi' => '<p><strong>☁️ CLOUD ENGINEERING INTERNSHIP ☁️</strong></p>
        <p>Pindahkan infrastruktur ke awan! Program magang Cloud Engineer akan membekalimu dengan keterampilan mengelola layanan berbasis cloud modern.</p>

        <p><strong>Scope Pekerjaan:</strong></p>
        <p>• Implementasi dan manajemen layanan <strong>AWS/Azure/GCP</strong><br>
        • Infrastructure as Code dengan Terraform/CloudFormation<br>
        • Cloud security dan cost optimization<br>
        • Setup CI/CD pipeline untuk deployment ke cloud</p>

        <p><strong>Persyaratan:</strong></p>
        <p>• Pemahaman dasar tentang <em>layanan cloud</em> dan networking<br>
        • Familiar dengan konsep virtualization<br>
        • Basic scripting skills (Python, Bash)</p>

        <p><em>Build scalable infrastructure in the cloud!</em></p>'
            ],
            [
                'judul' => 'Magang Game Developer',
                'deskripsi' => '<p><strong>🎮 GAME DEVELOPER INTERNSHIP 🎮</strong></p>
        <p>Buat game yang seru dan adiktif! Magang Game Developer memberimu kesempatan untuk belajar semua aspek pengembangan game modern.</p>

        <p><strong>Aktivitas Magang:</strong></p>
        <p>• Pengembangan game dengan <strong>Unity atau Unreal Engine</strong><br>
        • Implementasi game mechanics dan physics<br>
        • Optimasi performa dan debugging<br>
        • Testing dan quality assurance</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pengetahuan dasar tentang <em>pemrograman</em> (C#, C++)<br>
        • Pemahaman tentang game design principles<br>
        • Kreativitas dan problem-solving skills</p>

        <p><em>Turn your gaming passion into a career!</em></p>'
            ],
            [
                'judul' => 'Magang Blockchain Developer',
                'deskripsi' => '<p><strong>⛓️ BLOCKCHAIN DEVELOPER INTERNSHIP ⛓️</strong></p>
        <p>Jadi pionir teknologi terdesentralisasi! Magang di bidang blockchain akan memperkenalkanmu pada teknologi yang sedang mengubah dunia.</p>

        <p><strong>Yang Akan Kamu Pelajari:</strong></p>
        <p>• Pengembangan <strong>smart contracts</strong> dengan Solidity<br>
        • Implementasi aplikasi berbasis Ethereum/Hyperledger<br>
        • Integrasi Web3 dan konsep DApps<br>
        • Testing dan security audit untuk blockchain apps</p>

        <p><strong>Kualifikasi:</strong></p>
        <p>• Pemahaman dasar tentang <em>kriptografi</em> dan distributed systems<br>
        • Familiar dengan konsep blockchain<br>
        • Dasar-dasar pemrograman (JavaScript, Python)</p>

        <p><em>Build the decentralized future!</em></p>'
            ],
            [
                'judul' => 'Magang IoT Engineer',
                'deskripsi' => '<p><strong>📱 IOT ENGINEERING INTERNSHIP 📱</strong></p>
        <p>Hubungkan dunia fisik dan digital! Program magang IoT Engineer akan mengajarkanmu cara mengembangkan solusi Internet of Things untuk berbagai kebutuhan.</p>

        <p><strong>Lingkup Magang:</strong></p>
        <p>• Pengembangan prototype dengan <strong>mikrokontroler</strong> (Arduino, Raspberry Pi)<br>
        • Implementasi sensor networks dan data collection<br>
        • Integrasi perangkat dengan cloud platforms<br>
        • Pengembangan dashboard untuk monitoring IoT</p>

        <p><strong>Persyaratan:</strong></p>
        <p>• Pemahaman dasar tentang <em>elektronika</em><br>
        • Familiar dengan pemrograman embedded (C/C++, Python)<br>
        • Minat dalam hardware dan connectivity</p>

        <p><em>Connect everything in the physical world!</em></p>'
            ]
        ];

        $data = [];
        $counter = 1;

        foreach ($periodeList as $p) {
            $tahun = intval(substr($p->nama_periode, 0, 4));
            $jenis = strpos($p->nama_periode, 'Ganjil') !== false ? 'Ganjil' : 'Genap';

            $status = strpos($p->nama_periode, '2024/2025') === 0 ? 'Selesai' : 'Aktif';

            if ($jenis == 'Ganjil') {
                $start = Carbon::create($tahun, 7, 1);
            } else {
                $start = Carbon::create($tahun + 1, 1, 1);
            }

            for ($i = 0; $i < 50; $i++) { // 50 lowongan per periode
                // Pilih template lowongan secara acak
                $template = $lowonganTemplates[array_rand($lowonganTemplates)];
                
                // Pilih perusahaan secara acak
                $perusahaan = $perusahaanList->random();
                
                $id_waktu_magang = $waktuMagang[array_rand($waktuMagang)];
                $tanggal_posting = $start->copy()->subMonths(rand(2, 4))->addDays(rand(0, 20));
                $batas_akhir = $start->copy()->subDays(rand(10, 35));
                
                // Buat judul dengan format "Magang [Posisi] di [Nama Perusahaan]"
                $judul = $template['judul'] . ' di ' . $perusahaan->nama;

                $data[] = [
                    'id_lowongan' => $counter++,
                    'id_jenis_magang' => $jenisMagang[array_rand($jenisMagang)],
                    'id_perusahaan' => $perusahaan->id_perusahaan,
                    'id_daerah_magang' => $daerah[array_rand($daerah)],
                    'judul_lowongan' => $judul,
                    'deskripsi_lowongan' => $template['deskripsi'],
                    'tanggal_posting' => $tanggal_posting->format('Y-m-d'),
                    'batas_akhir_lamaran' => $batas_akhir->format('Y-m-d'),
                    'status' => $status,
                    'id_periode' => $p->id_periode,
                    'id_waktu_magang' => $id_waktu_magang,
                    'id_insentif' => $insentif[array_rand($insentif)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('t_lowongan_magang')->insert($data);
        $this->command->info('Berhasil menyeeder ' . count($data) . ' data lowongan magang');
    }
}