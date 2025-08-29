<?php

use App\Models\AttendanceRecord;
use App\Models\Location;
use App\Models\User;

it('can access location relationship on attendance record', function () {
    // Create test data
    $user = User::factory()->create();
    $location = Location::factory()->create();

    // Create attendance record with location
    $attendanceRecord = AttendanceRecord::create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'type' => 'check_in',
        'recorded_at' => now(),
        'method' => 'face_recognition',
    ]);

    // Test that the location relationship works
    expect($attendanceRecord->location)->not->toBeNull();
    expect($attendanceRecord->location->id)->toBe($location->id);
    expect($attendanceRecord->location->name)->toBe($location->name);
});

it('can handle null location_id', function () {
    // Create test data
    $user = User::factory()->create();

    // Create attendance record without location
    $attendanceRecord = AttendanceRecord::create([
        'user_id' => $user->id,
        'location_id' => null,
        'type' => 'check_in',
        'recorded_at' => now(),
        'method' => 'face_recognition',
    ]);

    // Test that null location is handled gracefully
    expect($attendanceRecord->location)->toBeNull();
});

it('can eager load location relationship', function () {
    // Create test data
    $user = User::factory()->create();
    $location = Location::factory()->create();

    // Create attendance record with location
    AttendanceRecord::create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'type' => 'check_in',
        'recorded_at' => now(),
        'method' => 'face_recognition',
    ]);

    // Test eager loading
    $recordWithLocation = AttendanceRecord::with('location')->first();

    expect($recordWithLocation->location)->not->toBeNull();
    expect($recordWithLocation->location->name)->toBe($location->name);
});
