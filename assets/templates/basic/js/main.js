"use strict";
(function ($) {
  // ==========================================
  //      Start Document Ready function
  // ==========================================
  $(document).ready(function () {
    function initStickySidebar() {
      if (window.innerWidth >= 1600) {
        var sidebar = new StickySidebar("#app-body-sidebar", {
          topSpacing: 80,
          bottomSpacing: 20,
          containerSelector: ".app-body-wrapper-dashboard",
          innerWrapperSelector: ".app-body-sidebar-wrapper",
        });
      }
    }

    window.initStickySidebar = initStickySidebar;

    if ($("#app-body-sidebar").length) {
      setTimeout(() => {
        window.initStickySidebar();
      }, 1000);
    }

    // ========================= Sticky Sidebar Js End ==============

    //============================ Scroll To Top Icon Js Start =========
    (() => {
      const btn = $(".scroll-top");
      $(window).on("scroll", function () {
        if ($(window).scrollTop() >= 100) {
          $(".header").addClass("fixed-header");
          btn.addClass("show");
        } else {
          $(".header").removeClass("fixed-header");
          btn.removeClass("show");
        }
      });

      btn.on("click", function (e) {
        e.preventDefault();
        $("html, body").animate(
          {
            scrollTop: 0,
          },
          "300"
        );
      });
    })();

    // ========================== Add Attribute For Bg Image Js Start =====================
    $(".bg-img").css("background-image", function () {
      return `url(${$(this).data("background-image")})`;
    });
    // ========================== Add Attribute For Bg Image Js End =====================

    // ================== Password Show Hide Js Start ==========
    $(document).on("click", ".toggle-password", function () {
      var input = $($(this).attr("id"));
      if (input.attr("type") == "password") {
        input.attr("type", "text");
        $(this).text() == "Show" ? $(this).text("Hide") : $(this).text("Show");
      } else {
        input.attr("type", "password");
        $(this).text() == "Hide" ? $(this).text("Show") : $(this).text("Hide");
      }
    });
    // =============== Password Show Hide Js End =================

    // ================== Sidebar Menu Js Start ===============
    // Sidebar Dropdown Menu Start
    $(".has-dropdown > a").click(function () {
      $(".sidebar-submenu").slideUp(200);
      if ($(this).parent().hasClass("active")) {
        $(".has-dropdown").removeClass("active");
        $(this).parent().removeClass("active");
      } else {
        $(".has-dropdown").removeClass("active");
        $(this).next(".sidebar-submenu").slideDown(200);
        $(this).parent().addClass("active");
      }
    });

    $(".has-dropdown.active").find(".sidebar-submenu").slideDown(200);

    // Sidebar Dropdown Menu End

    // Sidebar Icon & Overlay js
    $(".app-header-btn").on("click", function () {
      $(".sidebar-menu").addClass("show-sidebar");
      $(".sidebar-overlay").addClass("show");
      $("body").addClass("scroll-hide");
    });

    $(".sidebar-menu__close, .sidebar-overlay").on("click", function () {
      $(".sidebar-menu").removeClass("show-sidebar");
      $(".sidebar-overlay").removeClass("show");
      $("body").removeClass("scroll-hide");

    });

    // Navigate Sidebar Js
    $(".navigate-sidebar-btn").on("click", function () {
      $(this).toggleClass("show-toggle");
      $(".root").toggleClass("control-sidebar");
    });
    // Sidebar Icon & Overlay js
    // ===================== Sidebar Menu Js End =================

    //Plugin Customization Start

    // ========================= Select2 Js Start ==============
    (() => {
      // select initial
      $(".select2").each(function (index, element) {
        if (!$(element).parent().hasClass("select2-wrapper")) {
          $(element).wrap('<div class="select2-wrapper"></div>');
        }

        var config = {
          dropdownParent: $(element).closest(".select2-wrapper"),
        };

        // Check if the select element has multiple attribute
        if ($(element).attr("multiple")) {
          config.multiple = true;

          if ($(element).attr("data-tags") === "true") {
            config.tags = true;
            config.tokenSeparators = [",", " "];
            config.createTag = function (params) {
              return {
                id: "new:" + params.term,
                text: params.term,
                newTag: true,
              };
            };
          }
        }

        $(element).select2(config);
      });

      // select2 with image
      window.formatState = function (state) {
        if (!state.id) {
          return state.text;
        }
        var $state = $(
          '<span class="img-flag-inner"><img src="' +
          $(state.element).attr("data-src") +
          '" class="img-flag" /> ' +
          state.text +
          "</span>"
        );
        return $state;
      }
      $(".img-select2").select2({
        templateResult: formatState,
        templateSelection: formatState,
      });
    })();
    // ========================= Select2 Js End ==============

    // ========================= Slick Slider Js Start ==============
    (() => {
      const sliderConfig = {
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        speed: 1500,
        dots: true,
        pauseOnHover: true,
        arrows: false,
        prevArrow:
          '<button type="button" class="slick-prev"><i class="fas fa-long-arrow-left"></i></button>',
        nextArrow:
          '<button type="button" class="slick-next"><i class="fas fa-long-arrow-right"></i></button>',
      };

      if ($(".account-left-slider").length) {
        $(".account-left-slider").slick({
          ...sliderConfig,
          arrows: false,
          slidesToScroll: 1,
          dots: false,
          autoplay: true,
          centerMode: true,
          variableWidth: true,
        });
      }
    })();
    // ========================= Slick Slider Js End ===================

    // calculate height
    function setHeight(variable, selector) {
      let thisElement = document.getElementsByClassName(`${selector}`)[0];
      if (thisElement) {
        let thisHeight = thisElement.clientHeight;
        document.documentElement.style.setProperty(
          `${variable}`,
          `${thisHeight}px`
        );
      }
    }

    setHeight("--header-h", "header");
    setHeight("--dh-h", "app-header");

    window.addEventListener("resize", function () {
      setHeight("--header-h", "header");
    });

    // update bar
    function updateBar(navItem, tabNavContainer, barClass) {
      var width = navItem.outerWidth();
      var position = navItem.position().left;
      tabNavContainer.find(barClass).css({
        width: width + "px",
        left: position + "px",
      });
    }
    // update bar end

    // tab nav js
    $(".add-tab-nav").each(function (index) {
      var $tabNav = $(this);

      $tabNav.find(".nav-link").on("click", function () {
        updateBar($(this), $tabNav, ".tab__bar");
      });

      var activeNavItem = $tabNav.find(".nav-link.active");
      if (activeNavItem.length) {
        updateBar(activeNavItem, $tabNav, ".tab__bar");
      }
    });
    // tab nav js end

    // action switch js
    $(".action-switch").each(function (index) {
      var $actionSwitch = $(this);

      $actionSwitch.find(".action-switch-label").on("click", function () {
        updateBar($(this), $actionSwitch, ".action-switch-bar");
        $actionSwitch.find(".action-switch-label").removeClass("active");
        $(this).addClass("active");
      });

      var activeNavItem = $actionSwitch.find(".action-switch-label.active");
      if (activeNavItem.length) {
        updateBar(activeNavItem, $actionSwitch, ".action-switch-bar");
      }
    });
    // action switch js

    // upload file js
    $(".upload-inner-input").on("change", function (event) {
      const file = event.target.files[0];
      const parent = $(this).closest(".upload-inner");

      if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (e) =>
          parent.find(".upload-inner-img").attr("src", e.target.result);
        reader.readAsDataURL(file);
        parent.find(".upload-inner-preview").removeClass("d-none");
      } else {
        parent.find(".upload-inner-preview").addClass("d-none");
      }
    });

    $(".close-btn").on("click", function () {
      const parent = $(this).closest(".upload-inner-preview");
      parent.find(".upload-inner-img").attr("src", "");
      parent.addClass("d-none");
    });

    // tooltips
    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    const tooltipList = [...tooltipTriggerList].map(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );

    // dropdown menu control
    $(".table-wrapper").on(
      "click",
      'button[data-bs-toggle="dropdown"]',
      function (e) {
        const { top, left } = $(this)
          .next(".dropdown-menu")[0]
          .getBoundingClientRect();
        $(this)
          .next(".dropdown-menu")
          .css({
            position: "fixed",
            inset: "unset",
            transform: "unset",
            top: top + "px",
            left: left + "px",
          });
      }
    );

    if ($(".table-wrapper").length) {
      $(window).on("scroll", function (e) {
        $(".table-wrapper .dropdown-menu").removeClass("show");
        $('.table-wrapper button[data-bs-toggle="dropdown"]').removeClass(
          "show"
        );
      });
    }

    // dropdown menu close end
    $('button[data-bs-dismiss="dropdown"]').on("click", function (e) {
      e.preventDefault();
      $(this).closest(".dropdown-menu").removeClass("show");
      $(this).closest('button[data-bs-toggle="dropdown"]').removeClass("show");
    });

    // editor js
    if ($("#code-editor").length) {
      const editor = CodeMirror.fromTextArea(
        document.getElementById("code-editor"),
        {
          lineNumbers: true,
          mode: "javascript",
          theme: "default",
          tabSize: 2,
          indentWithTabs: true,
        }
      );

      const preview = document.getElementById("preview");
      function updatePreview() {
        preview.srcdoc = editor.getValue();
      }
      editor.on("change", updatePreview);
      updatePreview();
    }

    // calender js
    $(".calender-btn").on("click", function (e) {
      if ($(".datepicker2-range").length > 0) {
        $(".datepicker2-range").trigger("focus");
      }
    });

    // date picker
    $(".datepicker2-range").length > 0 &&
      $(".datepicker2-range").daterangepicker(
        {
          // singleDatePicker: true,
          showWeekNumbers: true,
          showISOWeekNumbers: true,
          applyButtonClasses: "btn btn--base btn--sm",
          cancelClass: "btn btn--secondary btn--sm",
          ranges: {
            Today: [moment(), moment()],
            Yesterday: [
              moment().subtract(1, "days"),
              moment().subtract(1, "days"),
            ],
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "Last 30 Days": [moment().subtract(29, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
              moment().subtract(1, "month").startOf("month"),
              moment().subtract(1, "month").endOf("month"),
            ],
          },
          alwaysShowCalendars: true,
        },
        function (start, end, label) {
          console.log(
            "New date range selected: " +
            start.format("YYYY-MM-DD") +
            " to " +
            end.format("YYYY-MM-DD") +
            " (predefined range: " +
            label +
            ")"
          );
        }
      );

    $(".datepicker2-range-max-today").length > 0 &&
      $(".datepicker2-range-max-today").daterangepicker(
        {
          showWeekNumbers: true,
          showISOWeekNumbers: true,
          applyButtonClasses: "btn btn--base btn--sm",
          cancelClass: "btn btn--secondary btn--sm",
          maxDate: moment(),
          ranges: {
            Today: [moment(), moment()],
            Yesterday: [
              moment().subtract(1, "days"),
              moment().subtract(1, "days"),
            ],
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "Last 30 Days": [moment().subtract(29, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
              moment().subtract(1, "month").startOf("month"),
              moment().subtract(1, "month").endOf("month"),
            ],
          },
          alwaysShowCalendars: true,
        }
      );

    $(".datepicker2-single-max-today").each(function () {
      var $picker = $(this);

      $picker.daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: true,
        autoApply: true,
        startDate: moment(),
        maxDate: moment(),
        locale: {
          format: 'MM/DD/YYYY'
        },
        opens: 'left'
      });

      // Fire change so listeners update grid and navigation state
      $picker.on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY')).trigger('change');
      });

      $picker.off('show.daterangepicker'); // optional: remove default show event
    });
    // full calender

    if ($("#calendar").length > 0) {
      var calendarEl = document.getElementById("calendar");
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",

        headerToolbar: {
          left: window.innerWidth < 768 ? "prev,next" : "prev,next today",
          center: "title",
          right:
            window.innerWidth < 768
              ? "dayGridMonth,timeGridDay"
              : "dayGridDay,dayGridWeek,dayGridMonth",
        },

        height: "auto",

        events: [
          {
            title: "Project Planning",
            start: "2025-11-05T09:30:00",
          },
          {
            title: "UI Review Session",
            start: "2025-11-05T14:00:00",
          },
          {
            title: "Client Call",
            start: "2025-11-05T17:00:00",
          },
          {
            title: "Team Meeting",
            start: "2025-11-06T10:00:00",
          },
          {
            title: "Design Work",
            start: "2025-11-08",
            end: "2025-11-10",
          },
          {
            title: "Client Presentation",
            start: "2025-11-09T14:00:00",
          },
          {
            title: "Development Sprint",
            start: "2025-11-11",
            end: "2025-11-13",
          },
          {
            title: "QA Testing",
            start: "2025-11-15T09:00:00",
          },
          {
            title: "Planning Meeting",
            start: "2025-11-16T11:00:00",
          },
        ],
      });
      calendar.render();
    }

    // full calender END

    // ========================= Popup Js Start ==============
    $(".magPopup").length &&
      $(".magPopup").magnificPopup({
        type: "image",
        gallery: {
          enabled: true,
        },
      });
    // ========================= Popup Js End ==============

    $('.calc-size').each(function () {
      let width = $(this).outerWidth();
      let height = $(this).outerHeight();
      $(this).css('--calc-width', width + 'px');
      $(this).css('--calc-height', height + 'px');
    });
  });

  // ==========================================
  //      End Document Ready function
  // ==========================================

  // ========================= Preloader Js Start =====================
  $(window).on("load", function () {
    $(".preloader").fadeOut();
  });
  // ========================= Preloader Js End=====================
})(jQuery);


function initLazy() {
  const lazyImages = document.querySelectorAll("img.lazy[data-src]");
  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.remove("lazy");
        img.removeAttribute("data-src");
        obs.unobserve(img);
      }
    });
  });

  lazyImages.forEach(img => observer.observe(img));
}

function getDateDuration(dateStr, inclusive = true) {
  let [start, end] = dateStr.split(' - ');
  let startDate = new Date(start);
  let endDate = new Date(end);
  let diff = (endDate - startDate) / (1000 * 60 * 60 * 24);
  return inclusive ? diff + 1 : diff;
}


// Lightbox scroll lock
$(function () {
    const overlay = document.querySelector('.lightboxOverlay');
    if (!overlay) return;

    let lastState = $(overlay).is(':visible');

    const observer = new MutationObserver(() => {
        const isVisible = $(overlay).is(':visible');

        if (isVisible !== lastState) {
            lastState = isVisible;

            if (isVisible) {
                $('body').addClass('scroll-hide');
            } else {
                $('body').removeClass('scroll-hide');
            }
        }
    });

    observer.observe(overlay, {
        attributes: true,
        attributeFilter: ['style', 'class']
    });
});
