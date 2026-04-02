/* Imani Cars — Main JavaScript (vanilla, no jQuery) */
'use strict';

(function () {

  /* =====================================================
     STICKY HEADER
     ===================================================== */
  var header = document.getElementById('ic-site-header');
  var spacer = document.querySelector('.ic-header-spacer');

  function setHeaderHeight() {
    if (header && spacer) {
      spacer.style.height = header.offsetHeight + 'px';
    }
  }

  setHeaderHeight();
  window.addEventListener('resize', setHeaderHeight);

  /* =====================================================
     HAMBURGER / MOBILE NAV TOGGLE
     ===================================================== */
  var hamburger = document.getElementById('ic-hamburger');
  var nav       = document.getElementById('ic-nav');

  if (hamburger && nav) {
    hamburger.addEventListener('click', function () {
      var isOpen = hamburger.getAttribute('aria-expanded') === 'true';
      hamburger.setAttribute('aria-expanded', String(!isOpen));
      nav.classList.toggle('ic-category-nav--open', !isOpen);
    });
  }

  /* =====================================================
     DROPDOWN MENUS (nav)
     ===================================================== */
  var dropdownTriggers = document.querySelectorAll('.ic-nav__link--dropdown');

  dropdownTriggers.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = btn.getAttribute('aria-expanded') === 'true';
      // Close all first
      dropdownTriggers.forEach(function (b) {
        b.setAttribute('aria-expanded', 'false');
        b.parentElement.classList.remove('ic-nav__item--open');
      });
      if (!isOpen) {
        btn.setAttribute('aria-expanded', 'true');
        btn.parentElement.classList.add('ic-nav__item--open');
      }
    });
  });

  // Close dropdowns on outside click
  document.addEventListener('click', function () {
    dropdownTriggers.forEach(function (b) {
      b.setAttribute('aria-expanded', 'false');
      b.parentElement.classList.remove('ic-nav__item--open');
    });
  });

  /* =====================================================
     SEARCH TABS (hero + header)
     ===================================================== */
  var searchTabs = document.querySelectorAll('.ic-search-tab');

  searchTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var tabGroup = tab.closest('.ic-search-tabs');
      if (!tabGroup) return;

      // Deactivate siblings
      tabGroup.querySelectorAll('.ic-search-tab').forEach(function (t) {
        t.classList.remove('ic-search-tab--active');
        t.setAttribute('aria-selected', 'false');
      });

      // Activate clicked
      tab.classList.add('ic-search-tab--active');
      tab.setAttribute('aria-selected', 'true');

      // Update hidden fields
      var form = tab.closest('.ic-search-card') && tab.closest('.ic-search-card').querySelector('form');
      if (!form) form = document.getElementById('ic-hero-search');
      if (!form) return;

      var conditionInput = form.querySelector('#ic-search-condition');
      var sellerInput    = form.querySelector('#ic-search-seller');
      var tabType        = tab.getAttribute('data-tab');

      if (conditionInput) conditionInput.value = '';
      if (sellerInput)    sellerInput.value    = '';

      switch (tabType) {
        case 'used':
          if (conditionInput) conditionInput.value = 'used';
          break;
        case 'new':
          if (conditionInput) conditionInput.value = 'new';
          break;
        case 'dealer':
          if (sellerInput) sellerInput.value = 'dealer';
          break;
        case 'private':
          if (sellerInput) sellerInput.value = 'private';
          break;
        case 'finance':
          form.action = (typeof IC !== 'undefined' ? IC.homeUrl : '/') + 'finance/';
          break;
        default:
          break;
      }
    });
  });

  /* =====================================================
     ADVANCED SEARCH FILTER TOGGLE
     ===================================================== */
  var advancedBtn     = document.getElementById('ic-toggle-advanced');
  var advancedFilters = document.getElementById('ic-advanced-filters');

  if (advancedBtn && advancedFilters) {
    advancedBtn.addEventListener('click', function () {
      var isOpen = advancedBtn.getAttribute('aria-expanded') === 'true';
      advancedBtn.setAttribute('aria-expanded', String(!isOpen));
      if (isOpen) {
        advancedFilters.setAttribute('hidden', '');
        advancedBtn.textContent = '+ More filters (price, year, body type)';
      } else {
        advancedFilters.removeAttribute('hidden');
        advancedBtn.textContent = '- Hide filters';
      }
    });
  }

  /* =====================================================
     CITY TABS (featured listings + dealers)
     ===================================================== */
  var cityTabs   = document.querySelectorAll('.ic-city-tab');
  var cityPanels = document.querySelectorAll('.ic-city-panel');

  cityTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var tabGroup = tab.closest('.ic-city-tabs');
      if (!tabGroup) return;
      var section  = tabGroup.closest('section') || tabGroup.parentElement;

      // Deactivate all tabs in this group
      tabGroup.querySelectorAll('.ic-city-tab').forEach(function (t) {
        t.classList.remove('ic-city-tab--active');
        t.setAttribute('aria-selected', 'false');
      });

      // Activate clicked
      tab.classList.add('ic-city-tab--active');
      tab.setAttribute('aria-selected', 'true');

      // Show matching panel
      var city = tab.getAttribute('data-city');
      section.querySelectorAll('.ic-city-panel').forEach(function (panel) {
        var isTarget = panel.id === 'ic-city-' + city;
        panel.classList.toggle('ic-city-panel--active', isTarget);
        panel.setAttribute('aria-hidden', isTarget ? 'false' : 'true');
      });
    });
  });

  /* =====================================================
     GALLERY THUMBS (single car page)
     ===================================================== */
  var mainImg    = document.getElementById('ic-gallery-main');
  var thumbBtns  = document.querySelectorAll('.ic-single-gallery__thumb-btn');

  thumbBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var newSrc = btn.getAttribute('data-img');
      if (mainImg && newSrc) {
        mainImg.src = newSrc;
      }
      thumbBtns.forEach(function (b) { b.classList.remove('ic-single-gallery__thumb-btn--active'); });
      btn.classList.add('ic-single-gallery__thumb-btn--active');
    });
  });

  /* =====================================================
     FAVOURITE / SAVE BUTTONS
     ===================================================== */
  function getFavourites() {
    try {
      return JSON.parse(localStorage.getItem('ic_favourites') || '[]');
    } catch (e) {
      return [];
    }
  }

  function saveFavourites(favs) {
    try {
      localStorage.setItem('ic_favourites', JSON.stringify(favs));
    } catch (e) {}
  }

  function initFavButtons() {
    var favBtns = document.querySelectorAll('.ic-car-card__fav');
    var favs    = getFavourites();

    favBtns.forEach(function (btn) {
      var id = btn.getAttribute('data-id');
      if (id && favs.indexOf(id) !== -1) {
        btn.classList.add('ic-car-card__fav--active');
        btn.querySelector('span').innerHTML = '&#9829;';
      }
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var currentFavs = getFavourites();
        var idx         = currentFavs.indexOf(id);
        if (idx === -1) {
          currentFavs.push(id);
          btn.classList.add('ic-car-card__fav--active');
          btn.querySelector('span').innerHTML = '&#9829;';
          btn.setAttribute('aria-label', 'Remove from favourites');
        } else {
          currentFavs.splice(idx, 1);
          btn.classList.remove('ic-car-card__fav--active');
          btn.querySelector('span').innerHTML = '&#9825;';
          btn.setAttribute('aria-label', 'Save to favourites');
        }
        saveFavourites(currentFavs);
      });
    });
  }

  initFavButtons();

  /* =====================================================
     AJAX ENQUIRY FORM
     ===================================================== */
  var enquiryForm = document.getElementById('ic-car-enquiry-form');
  if (enquiryForm) {
    enquiryForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg    = document.getElementById('ic-enquiry-msg');
      var data   = new FormData(enquiryForm);
      data.append('action', 'ic_enquiry');
      data.append('nonce',  (typeof IC !== 'undefined' ? IC.nonce : ''));

      var submitBtn = enquiryForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled    = true;
        submitBtn.textContent = 'Sending...';
      }

      fetch((typeof IC !== 'undefined' ? IC.ajaxUrl : '/wp-admin/admin-ajax.php'), {
        method: 'POST',
        body:   data,
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (msg) {
            msg.removeAttribute('hidden');
            msg.textContent  = res.data && res.data.message ? res.data.message : (res.success ? 'Sent!' : 'Error.');
            msg.className    = 'ic-form-msg ' + (res.success ? 'ic-form-msg--success' : 'ic-form-msg--error');
          }
          if (res.success) enquiryForm.reset();
        })
        .catch(function () {
          if (msg) {
            msg.removeAttribute('hidden');
            msg.textContent = 'Network error. Please try again.';
            msg.className   = 'ic-form-msg ic-form-msg--error';
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Send Enquiry';
          }
        });
    });
  }

  /* =====================================================
     AJAX DEALER SIGNUP FORM
     ===================================================== */
  var signupForm = document.getElementById('ic-dealer-signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var msg    = document.getElementById('ic-lyc-msg');
      var data   = new FormData(signupForm);
      data.append('action', 'ic_dealer_signup');
      data.append('nonce',  (typeof IC !== 'undefined' ? IC.nonce : ''));

      var submitBtn = signupForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled    = true;
        submitBtn.textContent = 'Submitting...';
      }

      fetch((typeof IC !== 'undefined' ? IC.ajaxUrl : '/wp-admin/admin-ajax.php'), {
        method: 'POST',
        body:   data,
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (msg) {
            msg.removeAttribute('hidden');
            msg.textContent = res.data && res.data.message ? res.data.message : (res.success ? 'Success!' : 'Error.');
            msg.className   = 'ic-form-msg ' + (res.success ? 'ic-form-msg--success' : 'ic-form-msg--error');
          }
          if (res.success) signupForm.reset();
        })
        .catch(function () {
          if (msg) {
            msg.removeAttribute('hidden');
            msg.textContent = 'Network error. Please try again.';
            msg.className   = 'ic-form-msg ic-form-msg--error';
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Register My Dealership Free';
          }
        });
    });
  }

  /* =====================================================
     PRICING ANNUAL TOGGLE
     ===================================================== */
  var billingToggle = document.getElementById('ic-billing-toggle');

  if (billingToggle) {
    billingToggle.addEventListener('click', function () {
      var isAnnual = billingToggle.getAttribute('aria-checked') === 'true';
      billingToggle.setAttribute('aria-checked', String(!isAnnual));
      billingToggle.classList.toggle('ic-pricing-toggle__btn--active', !isAnnual);

      var monthlyPrices = document.querySelectorAll('.ic-price-monthly');
      var annualPrices  = document.querySelectorAll('.ic-price-annual');

      monthlyPrices.forEach(function (el) {
        if (!isAnnual) {
          el.setAttribute('hidden', '');
        } else {
          el.removeAttribute('hidden');
        }
      });
      annualPrices.forEach(function (el) {
        if (!isAnnual) {
          el.removeAttribute('hidden');
        } else {
          el.setAttribute('hidden', '');
        }
      });
    });
  }

  /* =====================================================
     PRICING FAQ ACCORDION
     ===================================================== */
  var faqItems = document.querySelectorAll('.ic-faq-item__q');

  faqItems.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var isOpen  = btn.getAttribute('aria-expanded') === 'true';
      var itemId  = btn.getAttribute('aria-controls');
      var content = itemId ? document.getElementById(itemId) : btn.nextElementSibling;

      // Close all
      faqItems.forEach(function (b) {
        b.setAttribute('aria-expanded', 'false');
        var cid = b.getAttribute('aria-controls');
        var c   = cid ? document.getElementById(cid) : b.nextElementSibling;
        if (c) {
          c.setAttribute('hidden', '');
          var icon = b.querySelector('.ic-faq-item__icon');
          if (icon) icon.textContent = '+';
        }
      });

      // Toggle clicked
      if (!isOpen) {
        btn.setAttribute('aria-expanded', 'true');
        if (content) content.removeAttribute('hidden');
        var icon = btn.querySelector('.ic-faq-item__icon');
        if (icon) icon.textContent = '−';
      }
    });
  });

  /* =====================================================
     SCROLL REVEAL ANIMATION (lightweight)
     ===================================================== */
  if ('IntersectionObserver' in window) {
    var revealEls = document.querySelectorAll('.ic-car-card, .ic-type-tile, .ic-why__item, .ic-review-card, .ic-dealer-card, .ic-lyc-prop, .ic-lyc-step');
    var observer  = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('ic--revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    revealEls.forEach(function (el) {
      el.classList.add('ic--will-reveal');
      observer.observe(el);
    });
  }

  /* =====================================================
     MAKE → MODEL DYNAMIC (basic JS version)
     ===================================================== */
  var makeSelects = document.querySelectorAll('select[name="make"]');

  var modelsByMake = {
    'Toyota':       ['Camry', 'Corolla', 'RAV4', 'HiLux', 'LandCruiser', 'Kluger', 'Prado', 'Fortuner', 'Yaris', 'C-HR'],
    'Mazda':        ['Mazda2', 'Mazda3', 'Mazda6', 'CX-3', 'CX-5', 'CX-8', 'CX-9', 'BT-50'],
    'Ford':         ['Ranger', 'Everest', 'Escape', 'Puma', 'Bronco', 'Focus', 'Fiesta', 'Mustang'],
    'Hyundai':      ['i30', 'Tucson', 'Santa Fe', 'Venue', 'Kona', 'i20', 'Ioniq 5', 'Staria'],
    'Kia':          ['Sportage', 'Sorento', 'Cerato', 'Carnival', 'Stinger', 'EV6', 'Seltos', 'Niro'],
    'Nissan':       ['X-Trail', 'Patrol', 'Navara', 'Qashqai', 'Leaf', 'Pathfinder', 'Note'],
    'Mitsubishi':   ['ASX', 'Eclipse Cross', 'Outlander', 'Triton', 'Pajero', 'Pajero Sport'],
    'Honda':        ['CR-V', 'HR-V', 'Civic', 'Accord', 'Jazz', 'ZR-V'],
    'Subaru':       ['Outback', 'Forester', 'XV', 'WRX', 'Impreza', 'BRZ', 'Liberty'],
    'BMW':          ['X3', 'X5', '3 Series', '5 Series', 'X1', 'X7', 'iX'],
    'Mercedes-Benz':['C-Class', 'E-Class', 'GLC', 'GLE', 'A-Class', 'CLA', 'EQC'],
    'Audi':         ['A4', 'A6', 'Q3', 'Q5', 'Q7', 'Q8', 'e-tron'],
    'Volkswagen':   ['Golf', 'Tiguan', 'Touareg', 'Polo', 'T-Roc', 'Caddy', 'Amarok'],
    'Tesla':        ['Model 3', 'Model Y', 'Model S', 'Model X'],
    'Land Rover':   ['Defender', 'Discovery', 'Range Rover', 'Discovery Sport', 'Range Rover Sport'],
    'Jeep':         ['Wrangler', 'Grand Cherokee', 'Compass', 'Cherokee', 'Renegade'],
    'Isuzu':        ['D-Max', 'MU-X'],
    'MG':           ['MG3', 'HS', 'ZS', 'ZS EV', 'MG4'],
    'LDV':          ['T60', 'Deliver 9', 'Mifa 9', 'D90'],
  };

  makeSelects.forEach(function (makeSelect) {
    var form        = makeSelect.closest('form');
    var modelSelect = form ? form.querySelector('select[name="model"]') : null;
    if (!modelSelect) return;

    makeSelect.addEventListener('change', function () {
      var selectedMake = makeSelect.value;
      var models       = modelsByMake[selectedMake] || [];

      // Clear existing options
      modelSelect.innerHTML = '<option value="">' + (modelSelect.querySelector('option') ? modelSelect.querySelector('option').textContent : 'Any Model') + '</option>';

      models.forEach(function (m) {
        var opt   = document.createElement('option');
        opt.value = m;
        opt.textContent = m;
        modelSelect.appendChild(opt);
      });
    });
  });

})();
