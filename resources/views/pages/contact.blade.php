@extends('layouts.app')

@section('base')
<div class="page-section">
    <div class="container page-section-inner" style="max-width: 900px;">
        <div class="section-heading">
            <div class="section-kicker">Contact</div>
            <div class="section-title">Talk to the Novupay team</div>
            <div class="section-subtitle">
                Reach out for product questions, integration support, or partnership opportunities. We’ll route your message to the right team.
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card-elevated h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-3">
                            <box-icon name='envelope' color='#0560aa'></box-icon>
                            <span class="ms-2">Email</span>
                        </h3>
                        <p class="mb-2">
                            <strong>General Inquiries:</strong><br>
                            <a href="mailto:info@novupay.ph">info@novupay.ph</a>
                        </p>
                        <p class="mb-2">
                            <strong>Support:</strong><br>
                            <a href="mailto:support@novupay.ph">support@novupay.ph</a>
                        </p>
                        <p class="mb-0">
                            <strong>Business:</strong><br>
                            <a href="mailto:business@novupay.ph">business@novupay.ph</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card-elevated h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-3">
                            <box-icon name='phone' color='#0560aa'></box-icon>
                            <span class="ms-2">Phone</span>
                        </h3>
                        <p class="mb-2">
                            <strong>Customer Support:</strong><br>
                            <a href="tel:+639123456789">+63 912 345 6789</a>
                        </p>
                        <p class="mb-0">
                            <strong>Business Hours:</strong><br>
                            Monday - Friday: 9:00 AM - 6:00 PM<br>
                            Saturday: 9:00 AM - 1:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="card-elevated">
                <div class="card-body p-4">
                    <h3 class="mb-3">Send us a message</h3>
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Your name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="your.email@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" placeholder="What is this regarding?">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="5" placeholder="Tell us how we can help..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
