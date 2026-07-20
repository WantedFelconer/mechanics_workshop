function fetchAvailability(date) {
    var container = document.getElementById('mechanic-slots');
    container.innerHTML =
        '<div class="loading-dots"><span></span><span></span><span></span></div>';

    fetch('api_availability.php?date=' + encodeURIComponent(date))
        .then(function (res) { return res.json(); })
        .then(function (data) {
            renderMechanics(data);
            updateMechanicDropdown(data);
        })
        .catch(function (err) {
            console.error('Failed to fetch availability:', err);
            container.innerHTML =
                '<div class="empty-state"><div class="empty-icon">&#9888;</div><p>Could not load availability. Please try again.</p></div>';
        });
}

function getLevel(available, max) {
    var pct = available / max;
    if (pct >= 0.5) return 'green';
    if (pct > 0) return 'yellow';
    return 'red';
}

function getStatusText(available) {
    if (available >= 3) return 'Many slots available';
    if (available >= 1) return 'Limited slots left';
    return 'Fully booked';
}

function getSlotEmoji(available) {
    if (available >= 3) return '&#128994;';
    if (available >= 1) return '&#128992;';
    return '&#128308;';
}

function renderMechanics(data) {
    var container = document.getElementById('mechanic-slots');
    container.innerHTML = '';

    data.forEach(function (mech, index) {
        var level = getLevel(mech.available, mech.max_cars);
        var isFull = mech.available <= 0;
        var pct = mech.max_cars > 0 ? (mech.booked / mech.max_cars * 100) : 0;

        var card = document.createElement('div');
        card.className = 'mechanic-card' +
            (mech.available > 0 ? ' highlight' : '') +
            (isFull ? ' fully-booked' : '');
        card.style.animationDelay = (index * 0.07) + 's';

        var avatar = document.createElement('div');
        avatar.className = 'mech-avatar';
        avatar.textContent = mech.name.charAt(0);

        var name = document.createElement('h3');
        name.textContent = mech.name;

        var slotsDiv = document.createElement('div');
        slotsDiv.className = 'slots';

        var bar = document.createElement('div');
        bar.className = 'slot-bar';

        var filled = document.createElement('div');
        filled.className = 'slot-filled level-' + level;
        filled.style.width = pct + '%';

        var span = document.createElement('span');
        span.className = 'slot-count ' + (isFull ? 'full' : (level === 'yellow' ? 'limited' : 'available'));
        span.innerHTML = getSlotEmoji(mech.available) + ' ' + mech.available + '/' + mech.max_cars;

        var label = document.createElement('span');
        label.className = 'slot-label';
        label.textContent = getStatusText(mech.available);

        bar.appendChild(filled);
        slotsDiv.appendChild(bar);
        slotsDiv.appendChild(span);
        slotsDiv.appendChild(label);

        card.appendChild(avatar);
        card.appendChild(name);
        card.appendChild(slotsDiv);
        container.appendChild(card);
    });
}

function updateMechanicDropdown(data) {
    var select = document.getElementById('mechanic_id');
    select.innerHTML = '<option value="">-- Select Mechanic --</option>';

    data.forEach(function (mech) {
        var opt = document.createElement('option');
        opt.value = mech.id;
        if (mech.available <= 0) {
            opt.disabled = true;
            opt.textContent = mech.name + ' (Fully booked)';
        } else {
            opt.textContent = mech.name + ' (' + mech.available + ' slot' +
                (mech.available !== 1 ? 's' : '') + ' free)';
        }
        select.appendChild(opt);
    });
}

function validateForm() {
    var name = document.getElementById('client_name').value.trim();
    var address = document.getElementById('address').value.trim();
    var phone = document.getElementById('phone').value.trim();
    var license = document.getElementById('car_license').value.trim();
    var engine = document.getElementById('car_engine').value.trim();
    var date = document.getElementById('appointment_date').value;
    var mechanic = document.getElementById('mechanic_id').value;

    if (name === '') {
        alert('Please enter your name.');
        document.getElementById('client_name').focus();
        return false;
    }

    if (address === '') {
        alert('Please enter your address.');
        document.getElementById('address').focus();
        return false;
    }

    if (phone === '') {
        alert('Please enter your phone number.');
        document.getElementById('phone').focus();
        return false;
    }

    if (!/^\d+$/.test(phone)) {
        alert('Phone number must contain only digits.');
        document.getElementById('phone').focus();
        return false;
    }

    if (license === '') {
        alert('Please enter your car license number.');
        document.getElementById('car_license').focus();
        return false;
    }

    if (engine === '') {
        alert('Please enter your car engine number.');
        document.getElementById('car_engine').focus();
        return false;
    }

    if (!/^\d+$/.test(engine)) {
        alert('Car engine number must contain only digits.');
        document.getElementById('car_engine').focus();
        return false;
    }

    if (date === '') {
        alert('Please select an appointment date.');
        document.getElementById('appointment_date').focus();
        return false;
    }

    var selectedDate = new Date(date + 'T00:00:00');
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    if (selectedDate < today) {
        alert('Appointment date cannot be in the past.');
        document.getElementById('appointment_date').focus();
        return false;
    }

    if (mechanic === '') {
        alert('Please select a mechanic.');
        document.getElementById('mechanic_id').focus();
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    var availDate = document.getElementById('avail_date');
    var formDate = document.getElementById('appointment_date');

    if (!availDate || !formDate) return;

    formDate.value = availDate.value;
    fetchAvailability(availDate.value);

    availDate.addEventListener('change', function () {
        formDate.value = availDate.value;
        fetchAvailability(availDate.value);
    });

    formDate.addEventListener('change', function () {
        availDate.value = formDate.value;
        fetchAvailability(availDate.value);
    });
});