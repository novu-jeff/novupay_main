@extends('layouts.app')

@section('base')
<div class="page-section">
    <div class="container page-section-inner" style="max-width: 960px;">
        <div class="section-heading">
            <div class="section-kicker">Product demo</div>
            <div class="section-title">See Novupay as your payment aggregator</div>
            <div class="section-subtitle">
                Walk through real payment flows, dashboards, and operational views tailored to how your team collects and reconciles today.
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <box-icon name='calendar-check' color='#0560aa' size='48px'></box-icon>
                    </div>
                    <h4 class="mb-2">Schedule</h4>
                    <p class="mb-0">
                        Choose a convenient time that works for you. We're flexible and available.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <box-icon name='show' color='#0560aa' size='48px'></box-icon>
                    </div>
                    <h4 class="mb-2">Live demo</h4>
                    <p class="mb-0">
                        See Novupay in action with a personalized walkthrough of payment flows, merchant portals, and reporting.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="mb-3">
                        <box-icon name='question-mark' color='#0560aa' size='48px'></box-icon>
                    </div>
                    <h4 class="mb-2">Q&amp;A</h4>
                    <p class="mb-0">
                        Ask questions and get answers from our payment experts.
                    </p>
                </div>
            </div>
        </div>

        <div class="card-elevated">
            <div class="card-body p-5">
                <h3 class="mb-4">Request a demo</h3>
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="demo-name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="demo-name" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label for="demo-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="demo-email" placeholder="john@company.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="demo-company" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="demo-company" placeholder="Your Company">
                        </div>
                        <div class="col-md-6">
                            <label for="demo-phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="demo-phone" placeholder="+63 912 345 6789">
                        </div>
                        <div class="col-md-6">
                            <label for="demo-date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="demo-date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="demo-time" class="form-label">Preferred Time</label>
                            <select class="form-control" id="demo-time" required>
                                <option value="">Select time</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="demo-message" class="form-label">Additional Information</label>
                            <textarea class="form-control" id="demo-message" rows="4" placeholder="Tell us about your business and what you'd like to see in the demo..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 1.1rem;">
                                Schedule Demo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-5 text-center">
            <p class="mb-0">
                <strong>Or contact us directly:</strong><br>
                <a href="mailto:demo@novupay.ph">demo@novupay.ph</a> | 
                <a href="tel:+639123456789">+63 912 345 6789</a>
            </p>
        </div>
    </div>
</div>
@endsection
