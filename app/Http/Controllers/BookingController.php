<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DeviceCategory;
use App\Models\Task;
use App\Services\TaskAssignmentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    protected $taskAssignmentService;
    protected $notificationService;

    public function __construct(
        TaskAssignmentService $taskAssignmentService,
        NotificationService $notificationService
    ) {
        $this->taskAssignmentService = $taskAssignmentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Show booking type selection
     */
    public function index()
    {
        return view('bookings.index');
    }

    /**
     * Show service booking form
     */
    public function showServiceForm()
    {
        $categories = DeviceCategory::active()->get();
        return view('bookings.service', compact('categories'));
    }

    /**
     * Show repair booking form
     */
    public function showRepairForm()
    {
        $categories = DeviceCategory::active()->get();
        return view('bookings.repair', compact('categories'));
    }

    /**
     * Get service cost for selected category
     */
    public function getServiceCost(Request $request)
    {
        $category = DeviceCategory::find($request->category_id);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $deviceCount = $request->device_count ?? 1;
        $totalCost = $category->service_cost * $deviceCount;

        return response()->json([
            'service_cost_per_device' => $category->service_cost,
            'device_count' => $deviceCount,
            'total_cost' => $totalCost,
        ]);
    }

    /**
     * Store service booking
     */
    public function storeService(Request $request)
    {
        $request->validate([
            'device_category_id' => 'required|exists:device_categories,id',
            'device_count' => 'required|integer|min:1|max:100',
        ]);

        DB::beginTransaction();
        try {
            $category = DeviceCategory::find($request->device_category_id);
            $serviceCostTotal = $category->service_cost * $request->device_count;

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'device_category_id' => $request->device_category_id,
                'type' => 'service',
                'device_count' => $request->device_count,
                'service_cost_total' => $serviceCostTotal,
                'total_fee' => $serviceCostTotal,
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('bookings.payment', $booking->id)
                ->with('success', 'Service booking created! Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booking failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store repair booking
     */
    public function storeRepair(Request $request)
    {
        $request->validate([
            'device_category_id' => 'required|exists:device_categories,id',
            'device_brand' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'problem_description' => 'required|string',
            'problem_images.*' => 'nullable|image|max:5120', // 5MB max per image
            'needs_transport' => 'nullable|boolean',
            'transport_type' => 'required_if:needs_transport,1|in:pickup,delivery,both',
            'pickup_address' => 'required_if:needs_transport,1|string',
            'pickup_lat' => 'nullable|string',
            'pickup_lng' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $category = DeviceCategory::find($request->device_category_id);

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('problem_images')) {
                foreach ($request->file('problem_images') as $image) {
                    $path = $image->store('problem-images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Calculate transport fee if needed
            $transportFee = 0;
            $distanceKm = 0;

            if ($request->needs_transport && $request->pickup_lat && $request->pickup_lng) {
                // Workshop coordinates (replace with your actual coordinates)
                $workshopLat = -17.8252; // Harare coordinates as example
                $workshopLng = 31.0335;

                $distanceKm = $this->calculateDistance(
                    $workshopLat,
                    $workshopLng,
                    $request->pickup_lat,
                    $request->pickup_lng
                );

                $transportFee = $distanceKm * 0.75; // $0.75 per km
            }

            $diagnosticFee = 25.00; // Fixed diagnostic fee
            $totalFee = $diagnosticFee + $transportFee;

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'device_category_id' => $request->device_category_id,
                'type' => 'repair',
                'device_brand' => $request->device_brand,
                'device_model' => $request->device_model,
                'problem_description' => $request->problem_description,
                'problem_images' => $imagePaths,
                'needs_transport' => $request->needs_transport ?? false,
                'transport_type' => $request->transport_type,
                'pickup_address' => $request->pickup_address,
                'pickup_lat' => $request->pickup_lat,
                'pickup_lng' => $request->pickup_lng,
                'distance_km' => $distanceKm,
                'transport_fee' => $transportFee,
                'diagnostic_fee' => $diagnosticFee,
                'total_fee' => $totalFee,
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('bookings.payment', $booking->id)
                ->with('success', 'Repair booking created! Please proceed to payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booking failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show payment page
     */
    public function showPayment($bookingId)
    {
        $booking = Booking::with(['user', 'deviceCategory'])
            ->where('user_id', auth()->id())
            ->findOrFail($bookingId);

        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.success', $booking->id)
                ->with('info', 'This booking has already been paid.');
        }

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Process payment (simplified - integrate with real payment gateway)
     */
    public function processPayment(Request $request, $bookingId)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $booking = Booking::where('user_id', auth()->id())->findOrFail($bookingId);

            if ($booking->payment_status === 'paid') {
                return redirect()->route('bookings.success', $booking->id)
                    ->with('info', 'Payment already completed.');
            }

            // TODO: Integrate with actual payment gateway (Stripe, PayPal, etc.)
            // For now, we'll simulate successful payment

            $booking->update([
                'payment_status' => 'paid',
                'payment_reference' => 'PAY-' . strtoupper(uniqid()),
                'paid_at' => now(),
                'status' => 'confirmed',
            ]);

            // Generate unique Task ID
            $category = $booking->deviceCategory;
            $taskId = Task::generateTaskId(auth()->user()->name, $category->code);

            // Assign technician
            $technician = $this->taskAssignmentService->assignTechnician($category->id);

            if (!$technician) {
                throw new \Exception('No available technician at the moment. Please contact support.');
            }

            // Create task
            $task = Task::create([
                'task_id' => $taskId,
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'device_category_id' => $booking->device_category_id,
                'technician_id' => $technician->id,
                'type' => $booking->type,
                'device_brand' => $booking->device_brand,
                'device_model' => $booking->device_model,
                'problem_description' => $booking->problem_description,
                'problem_images' => $booking->problem_images,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            // Send notifications
            $this->notificationService->notifyTechnicianNewJob($task);
            $this->notificationService->notifyManagementNewJob($task);

            // Notify client with task details
            \App\Models\Notification::create([
                'user_id' => auth()->id(),
                'type' => 'booking_confirmed',
                'title' => 'Booking Confirmed',
                'message' => "Your booking has been confirmed! Task ID: {$taskId}. Technician: {$technician->name}. Please bring your device to the workshop.",
                'data' => [
                    'task_id' => $task->id,
                    'task_code' => $taskId,
                    'technician_name' => $technician->name,
                    'technician_phone' => $technician->phone,
                ],
            ]);

            DB::commit();

            return redirect()->route('bookings.success', $booking->id)
                ->with('success', 'Payment successful! Your Task ID is: ' . $taskId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Show booking success page
     */
    public function success($bookingId)
    {
        $booking = Booking::with(['user', 'deviceCategory', 'task.technician'])
            ->where('user_id', auth()->id())
            ->findOrFail($bookingId);

        return view('bookings.success', compact('booking'));
    }

    /**
     * Show user's bookings
     */
    public function myBookings()
    {
        $bookings = Booking::with(['deviceCategory', 'task'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.my-bookings', compact('bookings'));
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius in kilometers

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }
}
