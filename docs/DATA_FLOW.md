# ASPIRE Data Flow Documentation

## Observation → COT Rating → AI Feedback → Prediction → Decision

### Overview
This document describes the complete data flow for teacher evaluation in the ASPIRE system.

---

### Stage 1: Observation (Raw Classroom Data)

**Purpose:** Capture raw observation data from classroom visits

**Model:** `App\Models\Observation`

**Data Captured:**
- Teacher being observed
- Supervisor conducting observation
- Date, subject, grade/section
- Duration of observation
- Qualitative notes
- Status: pending → completed

**Key Relationships:**
```php
Observation → belongsTo → Teacher
Observation → belongsTo → User (supervisor)
Observation → hasMany → CotRating
```

**Service Method:**
```php
COTService::createObservation(array $data): Observation
```

---

### Stage 2: COT Rating (Scored Evaluation)

**Purpose:** Convert observation notes into quantifiable ratings

**Model:** `App\Models\CotRating`

**Data Captured:**
- Observation reference
- Rating category (instruction, assessment, classroom_management, content_knowledge)
- Score (0 - max_score)
- Comments per category

**Key Relationships:**
```php
CotRating → belongsTo → Observation
CotRating → hasOne → Feedback (human/supervisor)
CotRating → hasOne → AiFeedback (AI-generated)
```

**Service Method:**
```php
COTService::completeObservation(int $observationId, array $ratings): Observation
```

**Important Notes:**
- Each observation can have multiple COT ratings (one per category)
- Scores are stored as decimals with max_score for percentage calculation
- The `percentage()` method calculates (score/max_score) * 100

---

### Stage 3: AI Feedback (Machine-Generated Analysis)

**Purpose:** Generate AI-powered insights based on COT ratings

**Model:** `App\Models\AiFeedback`

**STRICT SEPARATION:**
- `ai_feedback` table = AI-generated feedback only
- `feedback` table = Human/supervisor feedback only

**Data Captured:**
- Analysis text (auto-generated)
- Recommendations array
- Identified strengths array
- Areas for improvement array
- Confidence score (0.0 - 1.0)
- Model version

**Service Method:**
```php
AIFeedbackService::generateFeedback(int $cotRatingId): AiFeedback
```

**Confidence Score Interpretation:**
- ≥ 0.85: High confidence - AI feedback reliable
- 0.60 - 0.85: Medium confidence - Review recommended
- < 0.60: Low confidence - Requires human review

**AI Logic:**
```
IF score ≥ 90% → "Excellent performance..."
IF score ≥ 80% → "Proficient performance..."
IF score ≥ 70% → "Developing performance..."
IF score ≥ 60% → "Beginning performance..."
IF score < 60% → "Requires immediate intervention..."
```

---

### Stage 4: Prediction (Outcome Forecasting)

**Purpose:** Predict future performance, promotion readiness, and training needs

**Model:** `App\Models\Prediction`

**Types:**
1. **Performance** - Predicts future performance trajectory
2. **Promotion** - Assesses readiness for advancement
3. **Training** - Identifies specific training needs

**Service Methods:**
```php
PredictionService::predictPerformance(int $teacherId): Prediction
PredictionService::predictPromotion(int $teacherId): ?Prediction
PredictionService::predictTrainingNeeds(int $teacherId): Prediction
```

**Factors Considered:**
- Average COT scores
- Trend direction (improving/stable/declining)
- Consistency across categories
- Total observations count
- Recent improvement rate

**Valid Until:**
- Performance predictions: 6 months
- Promotion predictions: 12 months
- Training predictions: 3 months

---

### Stage 5: Decision (Actionable Insights)

**Purpose:** Convert predictions into actionable decisions

**Actions Generated:**

| Score Range | Prediction Type | Recommended Action |
|-------------|-----------------|-------------------|
| ≥ 85%, improving | Performance | Prepare for advanced roles |
| ≥ 75%, stable | Performance | Continue current support |
| 60-75% | Performance | Implement coaching plan |
| < 60% | Performance | Urgent intervention plan |
| ≥ 90%, consistent | Promotion | Initiate promotion review |
| ≥ 85% | Promotion | Begin promotion prep |
| < 70% category | Training | Immediate training required |
| 70-80% category | Training | Developmental training |

---

## Model Relationships Summary

```
User (supervisor)
  ↓ hasMany
Observation (raw data)
  ↓ hasMany
CotRating (scored evaluation)
  ↓ hasOne
├── Feedback (human/supervisor feedback)
└── AiFeedback (AI-generated feedback)
  ↓ influences
Prediction (forecasting)
  ↓ informs
Decision (actionable outcome)
```

---

## API Endpoints

### Observations
- `GET /api/observations` - List observations
- `POST /api/observations` - Create observation
- `GET /api/observations/{id}` - Get observation
- `PUT /api/observations/{id}` - Update observation
- `PATCH /api/observations/{id}/complete` - Complete observation

### COT Ratings
- `GET /api/observations/{id}/ratings` - Get ratings for observation
- `POST /api/cot-ratings` - Create rating
- `PUT /api/cot-ratings/{id}` - Update rating

### AI Feedback
- `POST /api/cot-ratings/{id}/generate-ai-feedback` - Generate AI feedback
- `GET /api/cot-ratings/{id}/ai-feedback` - Get AI feedback

### Predictions
- `GET /api/teachers/{id}/predictions` - Get teacher predictions
- `POST /api/teachers/{id}/generate-predictions` - Generate new predictions

---

## Database Schema Reference

### observations
```sql
teacher_id (FK)
supervisor_id (FK)
observation_date
duration_minutes
subject
grade_section
notes
created_at / updated_at
```

### cot_ratings
```sql
observation_id (FK)
rating_category
score / max_score
comments
```

### ai_feedback
```sql
cot_rating_id (FK)
analysis (TEXT)
recommendations (JSON)
strengths (JSON)
areas_for_improvement (JSON)
confidence_score
created_at / updated_at
```

### predictions
```sql
teacher_id (FK)
prediction_type
predicted_outcome
confidence_level
factors_considered (JSON)
action_recommended
valid_until
status
```
