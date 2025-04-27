<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FATIMA HOME WORLD CENTER</title>
    <link rel="stylesheet" href="./index-style.css">
    <style>
        /* Full background slideshow styles */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }
        
        .slideshow-container {
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1; /* Place it behind all content */
        }
        
        .slide {
            display: none;
            position: absolute;
            width: 100%;
            height: 100%;
        }
        
        .slide img {
            width: 100%;
            height: 100%;
        }
        
        .active {
            display: block;
        }
        
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            margin-top: -22px;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            background-color: rgba(0, 0, 0, 0.3);
            z-index: 2;
        }
        
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }
        
        .prev {
            left: 0;
        }
        
        .prev:hover, .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        
        .dot-container {
            padding-top: 10px;
            bottom: 20px;
            width: 100%;
            text-align: center;
            z-index: 2;
        }
        
        .dot {
            cursor: pointer;
            height: 12px;
            width: 12px;
            margin: 0 5px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }
        
        .active-dot, .dot:hover {
            background-color: #717171;
        }
        
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2;
        }
        
        .pause-btn {
            background-color: rgba(76, 175, 80, 0.7);
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }

        /* Content styles */
        .content {
            position: relative;

            background-color: rgba(255, 255, 255, 0.8);
            text-align: center;
            z-index: 1;
        }

        .bedroom-container {
            position: relative;
            background-color: white;
            padding: 20px;
            z-index: 1;
        }
    </style>
</head>

<body>
<?php include './nav.php'; ?>

<div class="slideshow-container">
        <div class="slide active">
            <img src="./images/background.jpg" alt="Slide 1">
        </div>
        
        <div class="slide">
            <img src="./images/1.jpg" alt="Slide 2">
        </div>
        
        <div class="slide">
            <img src="./images/2.jpg" alt="Slide 3">
        </div>
        <div class="slide">
            <img src="./images/3.jpg" alt="Slide 4">
        </div>
        <div class="slide">
            <img src="./images/4.jpg" alt="Slide 5">
        </div>
        <div class="slide">
            <img src="./images/5.jpg" alt="Slide 6">
        </div>
        <div class="slide">
            <img src="./images/6.jpg" alt="Slide 7">
        </div>
        <div class="slide">
            <img src="./images/7.jpg" alt="Slide 8">
        </div>
        <div class="slide">
            <img src="./images/8.jpg" alt="Slide 9">
        </div>
        <div class="slide">
            <img src="./images/9.jpg" alt="Slide 10">
        </div>
        <div class="slide">
            <img src="./images/10.jpg" alt="Slide 11">
        </div>
        
    </div>
    
    <div class="dot-container">
        <span class="dot active-dot" onclick="currentSlide(0)"></span>
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
        <span class="dot" onclick="currentSlide(5)"></span>
        <span class="dot" onclick="currentSlide(6)"></span>
        <span class="dot" onclick="currentSlide(7)"></span>
        <span class="dot" onclick="currentSlide(8)"></span>
        <span class="dot" onclick="currentSlide(9)"></span>
        <span class="dot" onclick="currentSlide(10)"></span>
    </div>
    
    <div class="controls">
        <button class="pause-btn" onclick="toggleSlideshow()">Pause</button>
    </div>

    <div class="content">
        <h1>Discover A High-Quality Range Of <br> Wholesale Stylish Furniture In the Philippines</h1>

        <p>
            Timber Art Furniture's commitment to quality, expertise and service has seen us become a leading provider of
            furniture to Filipino retailers and their customers.You can choose from Bedroom, Dining, Living Room and
            Office Furniture Collections.
            <br><br>
            Shop our wholesale furniture online and place your orders today, or get in touch with our friendly staff if
            you have any questions or queries.
        </p>
    </div>
    <div class="bedroom-container">
        <h2>Bedroom</h2>
        <div class="bedroom-items">
            <div class="bedroom-item">
                <img src="./images/bedroom.jpg" alt="Bedroom Item 1">
                <div class="bedroom-content">
                    <h3>ELEGANT BEDROOM</h3>
                    <p>Style up your bedroom with our collection of Bedroom Furniture that will compliment your home.
                        From bed frames, bedsides, dressers and more.</p>
                    <a class="view-details" href="http://localhost/fatima/product-list.php?category=bedroom">View List</a>
                </div>
            </div>
            <h2>Dining</h2>
            <div class="bedroom-item">
                <img src="./images/diningroom.jpg" alt="dining Item 2">
                <div class="bedroom-content">
                    <h3>AWESOME DINING ROOM</h3>
                    <p>Spruce up your dining room with high-quality dining tables. We offer a range of elegant Dining
                        Set Furniture that suit your modern living.</p>
                        <a class="view-details" href="http://localhost/fatima/product-list.php?category=diningroom">View List</a>
                </div>
            </div>
            <h2>Living Room</h2>
            <div class="bedroom-item">
                <img src="./images/livingroom.jpg" alt="dining Item 2">
                <div class="bedroom-content">
                    <h3>ELEGANT LIVING ROOM</h3>
                    <p>Relax in style with our collection of Living Room furniture that will bring elegance and refinement to any living space.</p>
                    <a class="view-details" href="http://localhost/fatima/product-list.php?category=livingroom">View List</a>
                </div>
            </div>
            <h2>Office</h2>
            <div class="bedroom-item">
                <img src="./images/officeroom.jpg" alt="dining Item 2">
                <div class="bedroom-content">
                    <h3>ELEGANT LIVING ROOM</h3>
                    <p>Relax in style with our collection of Office Room furniture that will bring elegance and refinement to any living space.</p>
                    <a class="view-details" href="http://localhost/fatima/product-list.php?category=office">View List</a>
                </div>
            </div>
        </div>
    </div>
    <script>
         let slideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const pauseBtn = document.querySelector('.pause-btn');
        let slideshowInterval;
        let isPlaying = true;
        
        // Show the first slide
        showSlide(slideIndex);
        
        // Start the automatic slideshow
        startSlideshow();
        
        function startSlideshow() {
            slideshowInterval = setInterval(() => {
                changeSlide(1);
            }, 3000); // 3 seconds interval
        }
        
        function toggleSlideshow() {
            if (isPlaying) {
                clearInterval(slideshowInterval);
                pauseBtn.textContent = "Play";
            } else {
                startSlideshow();
                pauseBtn.textContent = "Pause";
            }
            isPlaying = !isPlaying;
        }
        
        function changeSlide(n) {
            // Reset the timer when manually changing slides
            if (isPlaying) {
                clearInterval(slideshowInterval);
                startSlideshow();
            }
            showSlide(slideIndex += n);
        }
        
        function currentSlide(n) {
            // Reset the timer when manually selecting a slide
            if (isPlaying) {
                clearInterval(slideshowInterval);
                startSlideshow();
            }
            showSlide(slideIndex = n);
        }
        
        function showSlide(n) {
            // Wrap around if past the end or beginning
            if (n >= slides.length) {slideIndex = 0}
            if (n < 0) {slideIndex = slides.length - 1}
            
            // Hide all slides
            for (let i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
                if (i < dots.length) {
                    dots[i].classList.remove('active-dot');
                }
            }
            
            // Show the current slide
            slides[slideIndex].classList.add('active');
            if (slideIndex < dots.length) {
                dots[slideIndex].classList.add('active-dot');
            }
        }
    </script>
</body>
</html>