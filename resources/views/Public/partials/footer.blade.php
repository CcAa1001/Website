{{-- Footer --}}
<footer class="footer_2 pt_100" style="background: url({{ asset('assets/images/footer_2_bg_2.jpg') }});">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-xl-3 col-md-6 col-lg-3 wow fadeInUp" data-wow-delay=".7s">
                <div class="footer_2_logo_area">
                    <a class="footer_logo" href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/tesdt.png') }}" alt="{{ config('app.name', 'Zenis') }}" class="img-fluid w-100">
                    </a>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sequi, distinctio molestiae error
                        ullam obcaecati dolorem inventore.</p>
                    <ul>
                        <li><span>Follow :</span></li>
                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>
            
           
        <div class="row">
            <div class="col-12">
                <div class="footer_copyright mt_75">
                    <p>Copyright @ <b>{{ config('app.name', 'Zenis') }}</b> {{ date('Y') }}. All rights reserved.</p>
                    <ul class="payment">
                        <li>Payment by :</li>
                        <li>
                            <img src="{{ asset('assets/images/footer_payment_icon_1.jpg') }}" alt="payment" class="img-fluid w-100">
                        </li>
                        <li>
                            <img src="{{ asset('assets/images/footer_payment_icon_2.jpg') }}" alt="payment" class="img-fluid w-100">
                        </li>
                        <li>
                            <img src="{{ asset('assets/images/footer_payment_icon_3.jpg') }}" alt="payment" class="img-fluid w-100">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
