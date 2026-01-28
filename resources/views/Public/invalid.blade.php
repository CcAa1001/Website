@extends('layouts.public')

@section('title', 'QR Code Tidak Valid')

@section('content')
<div class="invalid_qr_page">
    <div class="invalid_qr_content">
        <div class="invalid_icon">
            <i class="fas fa-qrcode"></i>
            <div class="invalid_x">
                <i class="fas fa-times"></i>
            </div>
        </div>
        
        <h2>QR Code Tidak Valid</h2>
        <p>{{ $message ?? 'QR Code yang Anda scan tidak valid atau sudah tidak aktif.' }}</p>
        
        <div class="invalid_tips">
            <h5>Tips:</h5>
            <ul>
                <li>Pastikan Anda scan QR code yang ada di meja</li>
                <li>Hubungi pelayan jika QR code rusak</li>
                <li>Coba scan ulang dengan pencahayaan yang baik</li>
            </ul>
        </div>
        
        <a href="{{ route('home') }}" class="btn_back_home">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
    </div>
</div>

<style>
.invalid_qr_page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
}

.invalid_qr_content {
    background: #fff;
    border-radius: 20px;
    padding: 50px 40px;
    text-align: center;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.invalid_icon {
    position: relative;
    width: 100px;
    height: 100px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}

.invalid_icon > i {
    font-size: 45px;
    color: #dee2e6;
}

.invalid_x {
    position: absolute;
    bottom: -5px;
    right: -5px;
    width: 36px;
    height: 36px;
    background: #e74c3c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
}

.invalid_x i {
    font-size: 16px;
    color: #fff;
}

.invalid_qr_content h2 {
    margin: 0 0 15px;
    font-size: 24px;
    color: #333;
}

.invalid_qr_content p {
    margin: 0 0 30px;
    font-size: 15px;
    color: #666;
    line-height: 1.6;
}

.invalid_tips {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    text-align: left;
}

.invalid_tips h5 {
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.invalid_tips ul {
    margin: 0;
    padding-left: 20px;
}

.invalid_tips li {
    font-size: 13px;
    color: #666;
    margin-bottom: 6px;
}

.invalid_tips li:last-child {
    margin-bottom: 0;
}

.btn_back_home {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 30px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
    color: #fff;
    border: none;
    border-radius: 25px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn_back_home:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(255, 107, 107, 0.4);
    color: #fff;
}

@media (max-width: 575px) {
    .invalid_qr_content {
        padding: 40px 25px;
    }
    
    .invalid_qr_content h2 {
        font-size: 20px;
    }
}
</style>
@endsection
