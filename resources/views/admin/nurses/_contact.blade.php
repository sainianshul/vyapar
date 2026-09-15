<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="ti ti-mail-fast text-primary me-2"></i>Contact Nurse
        </h3>
    </div>
    <div class="card-body">
        <form id="contact-nurse-form" action="{{ route('admin.nurses.contact', $user->id) }}" method="POST">
            @csrf

            <!-- Channel Selection -->
            <div class="mb-3">
                <label class="form-label fw-bold">Communication Channel</label>
                <div>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="channel" value="email" checked />
                        <span class="form-check-label">Email</span>
                    </label>
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="channel" value="sms" />
                        <span class="form-check-label">SMS Message</span>
                    </label>
                </div>
            </div>

            <!-- Subject (Only for Email) -->
            <div class="mb-3" id="subject-container">
                <label class="form-label required fw-bold">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Action required on your profile" />
            </div>

            <!-- Message Body -->
            <div class="mb-3">
                <label class="form-label required fw-bold">Message</label>
                <textarea name="message" class="form-control" rows="6" placeholder="Type your message here..."></textarea>
                <div class="text-muted small mt-2" id="sms-counter" style="display: none;">
                    Characters: <span id="char-count">0</span>/160
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" id="contact-submit-btn">
                    <span class="indicator-label">
                        <i class="ti ti-send me-1"></i> Send Message
                    </span>
                    <span class="indicator-progress" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span> Please wait...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Radio toggle logic
    $('input[name="channel"]').on('change', function() {
        if ($(this).val() === 'sms') {
            $('#subject-container').slideUp();
            $('#sms-counter').show();
        } else {
            $('#subject-container').slideDown();
            $('#sms-counter').hide();
        }
    });

    // SMS char counter logic
    $('textarea[name="message"]').on('input', function() {
        if ($('input[name="channel"]:checked').val() === 'sms') {
            let count = $(this).val().length;
            $('#char-count').text(count);
            if (count > 160) {
                $('#char-count').addClass('text-danger');
            } else {
                $('#char-count').removeClass('text-danger');
            }
        }
    });

    // Form submission logic
    $('#contact-nurse-form').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let btn = $('#contact-submit-btn');
        let label = btn.find('.indicator-label');
        let progress = btn.find('.indicator-progress');

        // Basic validation
        let channel = $('input[name="channel"]:checked').val();
        let subject = $('input[name="subject"]').val().trim();
        let message = $('textarea[name="message"]').val().trim();

        if (channel === 'email' && !subject) {
            Swal.fire('Validation Error', 'Subject is required for Email.', 'error');
            return;
        }
        if (!message) {
            Swal.fire('Validation Error', 'Message body is required.', 'error');
            return;
        }

        // Loading state
        btn.prop('disabled', true);
        label.hide();
        progress.show();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sent!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    form.trigger('reset');
                    $('#subject-container').slideDown();
                    $('#sms-counter').hide();
                    $('#char-count').text('0').removeClass('text-danger');
                } else {
                    Swal.fire('Error', response.message || 'Failed to send message.', 'error');
                }
            },
            error: function(xhr) {
                let msg = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Error', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                progress.hide();
                label.show();
            }
        });
    });
</script>
