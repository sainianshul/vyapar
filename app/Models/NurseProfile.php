<?php

namespace App\Models;

use App\Exceptions\Nurse\InvalidOnboardingStepException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NurseProfile extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::saving(function ($model) {
            $lng = (float) ($model->longitude ?? 0);
            $lat = (float) ($model->latitude ?? 0);
            $model->location = \Illuminate\Support\Facades\DB::raw("ST_GeomFromText('POINT({$lng} {$lat})', 4326)");
        });
    }

    // Status

    const STATUS_PENDING = 0;

    const STATUS_UNDER_REVIEW = 1;

    const STATUS_APPROVED = 2;

    const STATUS_REJECTED = 3;

    const STATUS_SUSPENDED = 4;


    // Gender
    const GENDER_MALE = 1;

    const GENDER_FEMALE = 2;

    const GENDER_OTHER = 3;

    // Week days
    const DAY_SUNDAY = 0;
    const DAY_MONDAY = 1;
    const DAY_TUESDAY = 2;
    const DAY_WEDNESDAY = 3;
    const DAY_THURSDAY = 4;
    const DAY_FRIDAY = 5;
    const DAY_SATURDAY = 6;


    // Onboarding Steps
    const STEP_BASIC_PROFILE = 1;
    const STEP_CARE_TYPES = 2;
    const STEP_EDUCATION = 3;
    const STEP_WORK_HISTORY = 4;
    const STEP_DOCUMENTS = 5;
    const STEP_SUBMIT_FOR_REVIEW = 6;

    protected $fillable = [
        'user_id',
        'nurse_id_number',
        'license_number',
        'license_date',
        'license_expiry_date',
        'years_of_experience',
        'bio',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'avg_rating',
        'total_reviews',
        'trust_score',
        'trust_score_updated_at',
        'total_bookings_completed',
        'total_bookings_cancelled',
        'total_reports',
        'resolved_reports',
        'latitude',
        'longitude',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'available_from',
        'available_to',
        'timezone',
        'available_days',
        'is_available',
        'rejection_reason',
        'suspension_reason',
        'approved_at',
        'approved_by',
        'onboarding_step',
        'is_onboarding_completed',
        'status',
        'can_reapply',
    ];

    protected $hidden = [
        'location',
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
        'gender' => 'integer',
        'avg_rating' => 'float',
        'total_reviews' => 'integer',
        'trust_score' => 'float',
        'total_bookings_completed' => 'integer',
        'total_bookings_cancelled' => 'integer',
        'total_reports' => 'integer',
        'resolved_reports' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'available_days' => 'array',
        'is_available' => 'boolean',
        'onboarding_step' => 'integer',
        'is_onboarding_completed' => 'boolean',
        'status' => 'integer',
        'license_date' => 'date',
        'license_expiry_date' => 'date',
        'approved_at' => 'datetime',
        'trust_score_updated_at' => 'datetime',
    ];

    // Status List
    public static function getStatusList()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public static function getGenderList()
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
            self::GENDER_OTHER => 'Other',
        ];
    }

    public static function getDaysList()
    {
        return [
            self::DAY_SUNDAY => 'Sunday',
            self::DAY_MONDAY => 'Monday',
            self::DAY_TUESDAY => 'Tuesday',
            self::DAY_WEDNESDAY => 'Wednesday',
            self::DAY_THURSDAY => 'Thursday',
            self::DAY_FRIDAY => 'Friday',
            self::DAY_SATURDAY => 'Saturday',
        ];
    }

    public static function getOnboardingStepList()
    {
        return [

            self::STEP_BASIC_PROFILE => 'Basic Profile',
            self::STEP_CARE_TYPES => 'Care Types',
            self::STEP_EDUCATION => 'Education',
            self::STEP_WORK_HISTORY => 'Work History',
            self::STEP_DOCUMENTS => 'Documents',
            self::STEP_SUBMIT_FOR_REVIEW => 'Submit For Review',
        ];
    }



    // Attributes 
    public function getStatusNameAttribute()
    {
        return self::getStatusList()[
            $this->status
        ] ?? 'Unknown';
    }

    public function getGenderNameAttribute()
    {
        return self::getGenderList()[
            $this->gender
        ] ?? 'Unknown';
    }

    public function getStepNameAttribute()
    {
        return self::getOnboardingStepList()[
            $this->onboarding_step
        ] ?? '';
    }


    // Query Scopes 
    public function scopeApproved(
        Builder $query
    ) {

        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeAvailable(
        Builder $query
    ) {

        return $query->where(
            'is_available',
            true
        );
    }



    // Status Checks
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function hasCompletedOnboarding()
    {
        return $this->is_onboarding_completed;
    }

    // Can Acess Step 
    public function canAccessStep($step)
    {
        return $step <= (
            $this->onboarding_step + 1
        );
    }


    public function markOnboardingCompleted()
    {
        $this->update([
            'is_onboarding_completed' => true,
            'onboarding_step' => self::STEP_SUBMIT_FOR_REVIEW,
        ]);
    }

    // Status Updates
    public function markAsUnderReview()
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function markAsApproved($approvedBy)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => null,
            'suspension_reason' => null,
        ]);
    }

    public function markAsRejected($reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsSuspended($reason)
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'suspension_reason' => $reason,
        ]);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function educations()
    {
        return $this->hasMany(
            NurseEducation::class,
            'nurse_id'
        );
    }

    public function workHistories()
    {
        return $this->hasMany(
            NurseWorkHistory::class,
            'nurse_id'
        );
    }

    public function documents()
    {
        return $this->hasMany(
            NurseDocument::class,
            'nurse_id'
        );
    }

    public function careTypes()
    {
        return $this->belongsToMany(
            CareType::class,
            'care_type_nurse',
            'nurse_id',
            'care_type_id'
        )->withPivot('status')->withTimestamps();
    }

    public function canSaveStep(int $step): void
    {
        // Under Review No Changes Allowed 
        if ($this->status === self::STATUS_UNDER_REVIEW) {
            throw new InvalidOnboardingStepException(
                'Profile is under review. No changes allowed.'
            );
        }

        // Approved No Changes Allowed 
        if ($this->status === self::STATUS_APPROVED) {
            throw new InvalidOnboardingStepException(
                'Profile already approved.'
            );
        }

        // State is not Under Review or Approved 
        if ($this->onboarding_step < self::STEP_SUBMIT_FOR_REVIEW) {
            if ($step > $this->onboarding_step) {
                throw new InvalidOnboardingStepException(
                    'Please complete previous steps (' . $this->onboarding_step . ') first.'
                );
            }
            return;
        }
    }

    public function getOnboardingResponse(): array
    {
        $response = [
            'is_completed' => $this->is_onboarding_completed,
        ];

        if (!$this->is_onboarding_completed) {
            $response['next_step'] = $this->onboarding_step;
            $response['next_step_name'] = self::getOnboardingStepList()[$this->onboarding_step] ?? null;
        }

        return $response;
    }

    public function updateOnboardingStep(int $step)
    {
        // If Already Completed No need to update
        if ($this->is_onboarding_completed) {
            return;
        }

        if ($this->onboarding_step < $step) {
            $this->update(['onboarding_step' => $step]);
        }
    }

    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'is_available' => $this->is_available,
            'avg_rating' => $this->avg_rating,
            'total_reviews' => $this->total_reviews,
            'trust_score' => $this->trust_score,
            'onboarding_step' => $this->onboarding_step,
            'onboarding_step_text' => $this->onboarding_step_text,
            'is_onboarding_completed' => $this->is_onboarding_completed,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
        ];
    }

    public function verifications()
    {
        return $this->hasMany(NurseProfileVerification::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'nurse_id');
    }
}

