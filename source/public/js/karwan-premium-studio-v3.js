(() => {
  "use strict";

  const doc = document;
  const win = window;
  const reducedMotion = win.matchMedia("(prefers-reduced-motion: reduce)");
  const finePointer = win.matchMedia("(hover: hover) and (pointer: fine)");

  const ready = (callback) => {
    if (doc.readyState === "loading") {
      doc.addEventListener("DOMContentLoaded", callback, { once: true });
    } else {
      callback();
    }
  };

  ready(() => {
    const header = doc.querySelector("[data-header]");
    const menuToggle = doc.querySelector("[data-menu-toggle]");
    const menu = doc.querySelector("[data-menu]");

    const syncHeader = () => {
      if (header) header.classList.toggle("is-scrolled", win.scrollY > 42);
    };

    syncHeader();
    win.addEventListener("scroll", syncHeader, { passive: true });

    if (menuToggle && menu) {
      menuToggle.addEventListener("click", () => {
        const open = menuToggle.getAttribute("aria-expanded") === "true";
        menuToggle.setAttribute("aria-expanded", String(!open));
        menu.classList.toggle("is-open", !open);
      });
    }

    const revealItems = [...doc.querySelectorAll("[data-reveal]")];
    if (reducedMotion.matches || !("IntersectionObserver" in win)) {
      revealItems.forEach((item) => item.classList.add("is-visible"));
    } else {
      const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          const group = revealItems.indexOf(entry.target);
          entry.target.style.transitionDelay = `${Math.min(group % 4, 3) * 70}ms`;
          entry.target.classList.add("is-visible");
          revealObserver.unobserve(entry.target);
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -7% 0px" });

      revealItems.forEach((item) => revealObserver.observe(item));
    }

    const processLine = doc.querySelector("[data-process-line]");
    if (processLine && "IntersectionObserver" in win && !reducedMotion.matches) {
      const lineObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.parentElement?.classList.add("is-visible");
          lineObserver.disconnect();
        });
      }, { threshold: 0.4 });
      lineObserver.observe(processLine);
    }

    const hero = doc.querySelector("[data-hero]");
    const heroImage = doc.querySelector("[data-hero-image]");
    if (hero && heroImage && finePointer.matches && !reducedMotion.matches) {
      let frame = 0;
      const moveHero = (event) => {
        if (frame) cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
          const rect = hero.getBoundingClientRect();
          const x = (event.clientX - rect.left) / rect.width - 0.5;
          const y = (event.clientY - rect.top) / rect.height - 0.5;
          heroImage.style.transform = `scale(1.08) translate3d(${x * -10}px, ${y * -8}px, 0)`;
        });
      };
      hero.addEventListener("pointermove", moveHero, { passive: true });
      hero.addEventListener("pointerleave", () => {
        heroImage.style.transform = "scale(1.08)";
      });
    }

    if (finePointer.matches && !reducedMotion.matches) {
      const canvas = doc.querySelector("[data-cursor-canvas]");
      const dot = doc.querySelector("[data-cursor-dot]");
      if (canvas instanceof HTMLCanvasElement && dot instanceof HTMLElement) {
        const context = canvas.getContext("2d");
        const points = [];
        let pointer = { x: win.innerWidth / 2, y: win.innerHeight / 2 };
        let visible = false;
        let animationFrame = 0;
        let dpr = Math.min(win.devicePixelRatio || 1, 2);

        const resize = () => {
          dpr = Math.min(win.devicePixelRatio || 1, 2);
          canvas.width = Math.floor(win.innerWidth * dpr);
          canvas.height = Math.floor(win.innerHeight * dpr);
          canvas.style.width = `${win.innerWidth}px`;
          canvas.style.height = `${win.innerHeight}px`;
          context?.setTransform(dpr, 0, 0, dpr, 0, 0);
        };

        const onMove = (event) => {
          pointer = { x: event.clientX, y: event.clientY };
          dot.style.left = `${pointer.x}px`;
          dot.style.top = `${pointer.y}px`;
          if (!visible) {
            visible = true;
            dot.style.opacity = "1";
          }
          points.push({ x: pointer.x, y: pointer.y, life: 1 });
          if (points.length > 28) points.shift();
        };

        const draw = () => {
          if (!context) return;
          context.clearRect(0, 0, win.innerWidth, win.innerHeight);
          for (let index = points.length - 1; index >= 0; index -= 1) {
            points[index].life -= 0.035;
            if (points[index].life <= 0) points.splice(index, 1);
          }

          if (points.length > 1) {
            context.save();
            context.lineCap = "round";
            context.lineJoin = "round";
            context.shadowBlur = 18;
            context.shadowColor = "rgba(249,203,32,.65)";
            for (let index = 1; index < points.length; index += 1) {
              const previous = points[index - 1];
              const current = points[index];
              const gradient = context.createLinearGradient(previous.x, previous.y, current.x, current.y);
              gradient.addColorStop(0, `rgba(179,92,34,${Math.max(previous.life * 0.12, 0)})`);
              gradient.addColorStop(1, `rgba(249,203,32,${Math.max(current.life * 0.7, 0)})`);
              context.beginPath();
              context.moveTo(previous.x, previous.y);
              context.lineTo(current.x, current.y);
              context.strokeStyle = gradient;
              context.lineWidth = Math.max(0.7, current.life * 2.2);
              context.stroke();
            }
            context.restore();
          }
          animationFrame = requestAnimationFrame(draw);
        };

        resize();
        win.addEventListener("resize", resize);
        win.addEventListener("pointermove", onMove, { passive: true });
        doc.addEventListener("mouseleave", () => {
          visible = false;
          dot.style.opacity = "0";
        });
        animationFrame = requestAnimationFrame(draw);

        win.addEventListener("pagehide", () => cancelAnimationFrame(animationFrame), { once: true });
      }

      doc.querySelectorAll(".magnetic").forEach((element) => {
        element.addEventListener("pointermove", (event) => {
          const rect = element.getBoundingClientRect();
          const x = event.clientX - rect.left - rect.width / 2;
          const y = event.clientY - rect.top - rect.height / 2;
          element.style.transform = `translate3d(${x * 0.11}px, ${y * 0.11}px, 0)`;
        });
        element.addEventListener("pointerleave", () => {
          element.style.transform = "";
        });
      });

      doc.querySelectorAll("[data-tilt]").forEach((element) => {
        element.addEventListener("pointermove", (event) => {
          const rect = element.getBoundingClientRect();
          const x = (event.clientX - rect.left) / rect.width - 0.5;
          const y = (event.clientY - rect.top) / rect.height - 0.5;
          element.style.transform = `perspective(1100px) rotateX(${y * -4}deg) rotateY(${x * 5}deg) translateY(-3px)`;
        });
        element.addEventListener("pointerleave", () => {
          element.style.transform = "";
        });
      });
    }

    doc.querySelectorAll("[data-count]").forEach((counter) => {
      const target = Number(counter.getAttribute("data-count") || 0);
      if (!Number.isFinite(target)) return;
      const duration = reducedMotion.matches ? 1 : 900;
      const start = performance.now();
      const animate = (time) => {
        const progress = Math.min((time - start) / duration, 1);
        counter.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3))).toString();
        if (progress < 1) requestAnimationFrame(animate);
      };
      requestAnimationFrame(animate);
    });

    const dashToggle = doc.querySelector("[data-dash-toggle]");
    const dashSidebar = doc.querySelector("[data-dash-sidebar]");
    if (dashToggle && dashSidebar) {
      dashToggle.addEventListener("click", () => dashSidebar.classList.toggle("is-open"));
    }

    const stepButtons = [...doc.querySelectorAll("[data-step-button]")];
    const formSteps = [...doc.querySelectorAll("[data-form-step]")];
    const nextButton = doc.querySelector("[data-next-step]");
    const previousButton = doc.querySelector("[data-prev-step]");
    const currentStepText = doc.querySelector("[data-current-step]");
    const progress = doc.querySelector("[data-form-progress]");
    let currentStep = 1;

    const updateStep = (step) => {
      currentStep = Math.max(1, Math.min(step, formSteps.length || 1));
      formSteps.forEach((item) => {
        item.classList.toggle("is-active", Number(item.getAttribute("data-form-step")) === currentStep);
      });
      stepButtons.forEach((button) => {
        const number = Number(button.getAttribute("data-step-button"));
        button.classList.toggle("is-active", number === currentStep);
        button.classList.toggle("is-complete", number < currentStep);
      });
      if (previousButton instanceof HTMLButtonElement) previousButton.disabled = currentStep === 1;
      if (nextButton instanceof HTMLButtonElement) {
        nextButton.querySelector("span").textContent = currentStep === formSteps.length ? "Submit inquiry" : "Continue";
      }
      if (currentStepText) currentStepText.textContent = String(currentStep);
      if (progress instanceof HTMLElement) progress.style.width = `${(currentStep / formSteps.length) * 100}%`;
    };

    stepButtons.forEach((button) => {
      button.addEventListener("click", () => updateStep(Number(button.getAttribute("data-step-button"))));
    });
    nextButton?.addEventListener("click", () => {
      if (currentStep < formSteps.length) updateStep(currentStep + 1);
      else nextButton.querySelector("span").textContent = "Inquiry ready";
    });
    previousButton?.addEventListener("click", () => updateStep(currentStep - 1));
    if (formSteps.length) updateStep(1);

    const textarea = doc.querySelector(".textarea-shell textarea");
    const charCount = doc.querySelector(".char-count");
    if (textarea instanceof HTMLTextAreaElement && charCount) {
      textarea.addEventListener("input", () => {
        charCount.textContent = `${Math.min(textarea.value.length, 500)} / 500`;
      });
    }

    const uploadZone = doc.querySelector("[data-upload-zone]");
    const filePreview = doc.querySelector("[data-file-preview]");
    if (uploadZone) {
      ["dragenter", "dragover"].forEach((type) => uploadZone.addEventListener(type, (event) => {
        event.preventDefault();
        uploadZone.classList.add("is-dragging");
      }));
      ["dragleave", "drop"].forEach((type) => uploadZone.addEventListener(type, (event) => {
        event.preventDefault();
        uploadZone.classList.remove("is-dragging");
        if (type === "drop" && filePreview instanceof HTMLElement) filePreview.hidden = false;
      }));
      const fileInput = uploadZone.querySelector('input[type="file"]');
      fileInput?.addEventListener("change", () => {
        if (filePreview instanceof HTMLElement) filePreview.hidden = false;
      });
    }
  });
})();
