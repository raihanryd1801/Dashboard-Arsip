<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Image to PDF Converter | NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Pustaka jsPDF murni untuk render gambar tanpa teks tambahan -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; min-width: 260px; max-width: 260px; position: sticky; top: 0; z-index: 1000; }
        .main-content { width: 100%; padding: 2rem; display: flex; flex-direction: column; min-height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        .dropdown-toggle-icon::after { content: '\25BC'; float: right; font-size: 10px; margin-top: 7px; transition: transform 0.2s;}
        .nav-link[aria-expanded="true"] .dropdown-toggle-icon::after { transform: rotate(180deg); }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .footer-wrapper { margin-top: auto; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <nav class="sidebar p-3" id="sidebar">
        <h5 class="py-3 text-center fw-bold text-white tracking-wide">DATA ARSIP MULTI COMPANY</h5>
        <div class="nav flex-column">
            <a class="nav-link" href="/">Dashboard Utama</a>
            <a class="nav-link" href="/firewall">Firewall & Sesi</a>
            <a class="nav-link active" href="/converter">Image to PDF Converter</a>
            <a class="nav-link" href="/arsip">Pusat Dokumen</a> 
            
            
            <div class="mt-3 mb-2 ms-3 text-uppercase text-white-50" style="font-size: 0.75rem; font-weight: bold;">Data Arsip Perusahaan</div>
            
            @if(isset($menu_sidebar))
                @foreach($menu_sidebar as $pt => $kategoris)
                    @php 
                        $ptId = \Illuminate\Support\Str::slug($pt); 
                        $isPtActive = (isset($active_pt) && $active_pt == $pt); 
                    @endphp
                    
                    <a class="nav-link {{ $isPtActive ? 'text-white active' : 'text-white-50' }}"
                        data-bs-toggle="collapse"
                        href="#pt-{{ $ptId }}"
                        role="button"
                        aria-expanded="{{ $isPtActive ? 'true' : 'false' }}">
                        <span>{{ $pt }}</span>
                        <span class="dropdown-toggle-icon"></span>
                    </a>
                    
                    <div class="collapse {{ $isPtActive ? 'show' : '' }}" id="pt-{{ $ptId }}">
                        <div class="ms-3 mt-1 border-start border-secondary ps-2 mb-2">
                            @foreach($kategoris as $item)
                                <a class="nav-link py-1 text-white-50" href="/arsip/{{ rawurlencode($pt) }}/{{ rawurlencode($item->kategori) }}" style="font-size: 0.85rem;">
                                    📄 {{ $item->kategori }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold">Image to PDF Converter</h2>
            <form action="/logout" method="POST" class="mb-0">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <h5 class="card-title text-primary fw-bold mb-3">Ubah Gambar (JPG / PNG) Menjadi PDF Murni</h5>
                    <p class="text-muted small">Alat ini akan mengubah file gambar kamu langsung menjadi dokumen PDF bersih 100% tanpa ada coretan atau tulisan tambahan apa pun.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File Gambar (.jpg, .jpeg, .png)</label>
                        <input type="file" id="inputFile" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>

                    <div id="previewArea" class="p-3 bg-light rounded mb-3 border text-center d-none">
                        <p class="mb-2 fw-bold text-success small">Pratinjau Gambar:</p>
                        <img id="imgPreview" src="" alt="Preview" style="max-height: 200px; max-width: 100%; border-radius: 6px;">
                    </div>

                    <button type="button" id="btnConvert" class="btn btn-dark w-100 py-2" onclick="convertToPurePDF()">Download PDF Bersih</button>
                </div>
            </div>
        </div>

        <div class="footer-wrapper text-center mt-5 pt-3 border-top text-muted small">
            &copy; {{ date('Y') }} NOC PT. Dankom Mitra Abadi. All rights reserved.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('inputFile').addEventListener('change', function(e) {
        let file = e.target.files[0];
        if(file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('previewArea').classList.remove('d-none');
                document.getElementById('imgPreview').src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    async function convertToPurePDF() {
        let fileInput = document.getElementById('inputFile');
        if(fileInput.files.length === 0) {
            alert('Silakan pilih file gambar terlebih dahulu!');
            return;
        }

        let file = fileInput.files[0];
        let reader = new FileReader();

        reader.onload = async function(event) {
            let imgData = event.target.result;
            
            let img = new Image();
            img.src = imgData;
            
            img.onload = async function() {
                const { jsPDF } = window.jspdf;
                
                let orientation = img.width > img.height ? 'l' : 'p';
                let pdf = new jsPDF(orientation, 'mm', 'a4');
                
                let pageWidth = pdf.internal.pageSize.getWidth();
                let pageHeight = pdf.internal.pageSize.getHeight();
                
                let imgWidth = pageWidth;
                let imgHeight = (img.height * pageWidth) / img.width;
                
                if (imgHeight > pageHeight) {
                    imgHeight = pageHeight;
                    imgWidth = (img.width * pageHeight) / img.height;
                }
                
                let x = (pageWidth - imgWidth) / 2;
                let y = (pageHeight - imgHeight) / 2;
                
                let imageType = file.type === 'png' ? 'PNG' : 'JPEG';
                pdf.addImage(imgData, imageType, x, y, imgWidth, imgHeight);
                
                let cleanName = file.name.substring(0, file.name.lastIndexOf('.'));
                pdf.save(cleanName + '_bersih.pdf');
            }
        };

        reader.readAsDataURL(file);
    }
</script>
</body>
</html>