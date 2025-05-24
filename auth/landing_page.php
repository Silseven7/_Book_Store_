<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ShelfShare Landing Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&display=swap" rel="stylesheet">

  
  <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
  <style>
    .handwriting {
      font-family: 'Caveat', cursive;
      letter-spacing: 0.5px;
      line-height: 1.8;
    }
  </style>
</head>
<body class="scroll-smooth">

  
  <section
    class="h-screen bg-cover bg-center flex flex-col justify-center items-center text-white relative"
    style="background-image: url('https://wallpapercave.com/wp/wp6974213.jpg');"
    id="hero"
  >
    <div id="heroContent" class="text-center">
      <h1 class="text-5xl font-bold mb-6">Welcome to ShelfShare</h1>
      <div class="space-x-4">
        <a href="/_Book_Store_/login_form" class="bg-white text-black px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">Login</a>
        <a href="/_Book_Store_/enroll_form" class="bg-transparent border border-white px-6 py-2 rounded-full font-semibold hover:bg-white hover:text-black transition">Enroll</a>
      </div>
    </div>

    
    <div class="mt-20 animate-bounce cursor-pointer absolute bottom-10" id="scrollButton">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  </section>

  
  <section id="about" class="bg-cover bg-center py-20 px-8 relative" style="background-image: url('https://media.istockphoto.com/id/182174182/photo/open-book.jpg?s=612x612&w=0&k=20&c=TR24c5VK-PdCsjhxQdh7NOiLGCl0rzSdQlY7vKaM79M=');">
    <div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
      
      <div class="flex justify-center" id="aboutImageWrapper">
        <img src="https://i.imgur.com/4haWgJD.jpeg" 
             alt="Bookshelf" 
             class="rounded-2xl shadow-lg w-full max-w-md"
             id="aboutImage">
      </div>
  
      
      <div id="aboutText" class="p-8">
        <h3 class="text-4xl font-bold mb-6 text-white handwriting">Why We Built This</h3>
        <p class="text-2xl text-white mb-6 handwriting">
          ShelfShare is a platform designed to foster a community of readers, enabling users to share their literary insights and write thoughtful reviews for a wide range of books. Created to inspire and encourage a love of reading, ShelfShare serves as a hub for meaningful book discussions and discovery.
        </p>
        <p class="text-2xl text-white handwriting italic">
          This platform was developed by Suay Ilseven and Batuhan Ekinci. All rights reserved.
        </p>
      </div>
    </div>
  </section>

  
  <script>
    
    gsap.registerPlugin(ScrollTrigger);

    
    gsap.to("#heroContent", {
      opacity: 0,
      y: -50,
      duration: 1, 
      scrollTrigger: {
        trigger: "#hero",            
        start: "top top",            
        end: "top -100%",             
        scrub: 1,                    
        toggleActions: "play reverse play reverse" 
      }
    });

    
    gsap.to("#hero", {
      backgroundPositionY: "-30%",  
      scrollTrigger: {
        trigger: "#hero",       
        start: "top top",            
        end: "bottom top",           
        scrub: 0.5,                  
        markers: false               
      }
    });

   
    gsap.from("#aboutImageWrapper", {
      y: 100,  
      opacity: 0,  
      duration: 1,  
      scrollTrigger: {
        trigger: "#about",           
        start: "top 80%",             
        end: "top 30%",               
        scrub: 0.5,                   
        markers: false                
      }
    });

    
    gsap.from("#aboutText", {
      y: 50,    
      opacity: 0,  
      duration: 1,  
      scrollTrigger: {
        trigger: "#about",           
        start: "top 80%",             
        end: "top 30%",               
        scrub: 0.5,                   
        markers: false                
      }
    });

    
    document.getElementById('scrollButton').addEventListener('click', function() {
      document.getElementById('about').scrollIntoView({ behavior: 'smooth' });
    });
  </script>
</body>
</html>
<!--jus test if github working.-->