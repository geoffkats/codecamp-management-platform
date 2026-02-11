    // Course bulk award
    public $showCourseBulkModal = false;
    public $courseBulkCourseId = null;
    public $courseBulkPoints = 0;
    public $courseBulkReason = '';
    
    public function openCourseBulkModal($courseId = null)
    {
        $this->courseBulkCourseId = $courseId ?? $this->courseFilter;
        $this->courseBulkPoints = 0;
        $this->courseBulkReason = '';
        $this->showCourseBulkModal = true;
    }
    
    public function closeCourseBulkModal()
    {
        $this->showCourseBulkModal = false;
        $this->reset(['courseBulkCourseId', 'courseBulkPoints', 'courseBulkReason']);
    }
    
    public function awardCourseXp()
    {
        $this->validate([
            'courseBulkCourseId' => 'required|exists:courses,id',
            'courseBulkPoints' => 'required|integer|min:1|max:10000',
            'courseBulkReason' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get all students enrolled in the course
            $students = User::whereHas('roles', function($q) {
                $q->where('name', 'student');
            })->whereHas('enrollments', function($q) {
                $q->where('course_id', $this->courseBulkCourseId)
                  ->where('status', 'approved');
            })->get();
            
            $count = 0;
            foreach ($students as $student) {
                // Award points
                $student->points()->updateOrCreate(
                    ['user_id' => $student->id],
                    ['total_points' => DB::raw('total_points + ' . $this->courseBulkPoints)]
                );
                
                // Log in user_progress
                UserProgress::create([
                    'user_id' => $student->id,
                    'course_id' => $this->courseBulkCourseId,
                    'type' => 'bulk_award',
                    'points_earned' => $this->courseBulkPoints,
                    'notes' => $this->courseBulkReason ?: 'Bulk course award by admin',
                ]);
                
                $count++;
            }
            
            DB::commit();
            
            $course = Course::find($this->courseBulkCourseId);
            session()->flash('message', "Successfully awarded {$this->courseBulkPoints} XP to {$count} students in {$course->title}");
            
            $this->closeCourseBulkModal();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course bulk XP award failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to award XP. Please try again.');
        }
    }
