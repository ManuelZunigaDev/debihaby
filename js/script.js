// DebiHaby - Interactive JavaScript

// Wait for DOM to load
document.addEventListener("DOMContentLoaded", () => {
  // === Mobile Menu Toggle ===
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const navMenu = document.querySelector(".nav-menu");

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("active");

      // Animate icon
      const icon = mobileMenuToggle.querySelector("i") || mobileMenuToggle;
      if (navMenu.classList.contains("active")) {
        icon.textContent = "✕";
      } else {
        icon.textContent = "☰";
      }
    });

    // Close menu when clicking on a link
    const navLinks = document.querySelectorAll(".nav-menu a");
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
        mobileMenuToggle.querySelector("i").textContent = "☰";
      });
    });
  }

  // === Smooth Scroll for Navigation Links ===
  const smoothScrollLinks = document.querySelectorAll('a[href^="#"]');
  smoothScrollLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const href = link.getAttribute("href");
      if (href === "#") return;

      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        const headerOffset = 80;
        const elementPosition = target.offsetTop;
        const offsetPosition = elementPosition - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: "smooth",
        });
      }
    });
  });

  // === Scroll Animations (Intersection Observer) ===
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      }
    });
  }, observerOptions);

  // Observe all fade-in elements
  const fadeElements = document.querySelectorAll(".fade-in");
  fadeElements.forEach((el) => observer.observe(el));

  // === Header Scroll Effect ===
  let lastScroll = 0;
  const header = document.querySelector(".header");

  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      header.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.1)";
    } else {
      header.style.boxShadow = "0 2px 8px rgba(0, 0, 0, 0.08)";
    }

    lastScroll = currentScroll;
  });

  // === Video Lazy Loading ===
  const videoPlaceholder = document.querySelector(".video-placeholder");
  if (videoPlaceholder) {
    videoPlaceholder.addEventListener("click", () => {
      const videoWrapper = videoPlaceholder.parentElement;
      const videoUrl = videoPlaceholder.dataset.video;

      if (videoUrl) {
        // Create iframe for YouTube/Vimeo
        const iframe = document.createElement("iframe");
        iframe.src = videoUrl;
        iframe.allowFullscreen = true;
        iframe.allow =
          "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
        videoWrapper.innerHTML = "";
        videoWrapper.appendChild(iframe);
      }
    });
  }

  // // === Card Hover 3D Effect ===
  // const cards = document.querySelectorAll(".access-card, .objective-card");

  // cards.forEach((card) => {
  //   card.addEventListener("mouseenter", (e) => {
  //     card.style.transition = "transform 0.1s ease-out";
  //   });

  //   card.addEventListener("mousemove", (e) => {
  //     const rect = card.getBoundingClientRect();
  //     const x = e.clientX - rect.left;
  //     const y = e.clientY - rect.top;

  //     const centerX = rect.width / 2;
  //     const centerY = rect.height / 2;

  //     const rotateX = (y - centerY) / 20;
  //     const rotateY = (centerX - x) / 20;

  //     card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
  //   });

  //   card.addEventListener("mouseleave", () => {
  //     card.style.transition = "transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)";
  //     card.style.transform =
  //       "perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)";
  //   });
  // });

  // === Parallax Effect for Hero - DISABLED ===
  // Removed to fix content overlap issue
  /*
  const hero = document.querySelector(".hero");
  if (hero) {
    window.addEventListener("scroll", () => {
      const scrolled = window.pageYOffset;
      const parallax = scrolled * 0.05;
      hero.style.transform = `translateY(${parallax}px)`;
    });
  }
  */

  // === Dynamic Year in Footer ===
  const yearElement = document.getElementById("current-year");
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }

  // === Animate Numbers (Counter Effect) ===
  const animateNumbers = (element) => {
    const target = parseInt(element.dataset.target);
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;

    const updateNumber = () => {
      current += increment;
      if (current < target) {
        element.textContent = Math.floor(current);
        requestAnimationFrame(updateNumber);
      } else {
        element.textContent = target;
      }
    };

    updateNumber();
  };

  // Observe number counters
  const numberCounters = document.querySelectorAll("[data-target]");
  const numberObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting && !entry.target.classList.contains("counted")) {
        entry.target.classList.add("counted");
        animateNumbers(entry.target);
      }
    });
  });

  numberCounters.forEach((counter) => numberObserver.observe(counter));

  // === Console Welcome Message ===
  console.log(
    "%c🎓 DebiHaby - Aprende Contabilidad Jugando",
    "color: #6C5CE7; font-size: 20px; font-weight: bold;"
  );
  console.log(
    '%cCBTis 171 "Mariano Abasolo"',
    "color: #0984e3; font-size: 14px;"
  );
});
