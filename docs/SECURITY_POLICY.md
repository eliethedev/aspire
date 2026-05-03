# ASPIRE Security Policy: User Registration and Role Management

## Overview

This document outlines the security policies implemented for user registration and role management in the ASPIRE (DepEd Teacher Supervision System) to prevent privilege escalation and ensure compliance with DepEd requirements.

## Registration Security Policy

### Strict Requirements

1. **Teacher-Only Self-Registration**: Only users with the role "teacher" are allowed to self-register via the public registration form.

2. **No Role Selection**: Users cannot choose their own role during self-registration. The role is automatically forced to "teacher".

3. **Automatic Role Assignment**: The system automatically assigns the "teacher" role using Spatie Laravel Permission: `$user->assignRole('teacher')`.

4. **School Validation**: All registrations must include a valid `school_id` that exists in the database.

5. **Higher Role Restrictions**: Users with higher roles (supervisor, school_head, division_admin, super_admin) can ONLY be created by authorized users through the admin panel.

### Security Measures Implemented

#### 1. Registration Form Security
- **Removed role selection dropdown** from the registration form
- **Added required school selection** with validation against existing schools
- **Teacher information fields** are always visible (department, years of service)
- **No role parameter** is accepted in the registration request

#### 2. Controller-Level Security
- **Forced role assignment**: `'role' => 'teacher'` cannot be overridden
- **Validation rules**: All required fields are validated
- **Spatie permission integration**: Proper role assignment using `assignRole()`
- **Teacher profile creation**: Automatic creation of associated teacher profile

#### 3. Request Validation Security
- **RegistrationRequest class** with comprehensive validation
- **Suspicious pattern detection**: Blocks admin-like identifiers in email/name
- **Role parameter rejection**: Any attempt to submit a role parameter is blocked
- **Custom error messages**: Clear security-related error messages

#### 4. Authorization for Higher Roles
- **UserManagementController**: Separate controller for admin user management
- **Role-based authorization**: Different authorization levels for different roles
- **School-scoped permissions**: School heads can only manage users in their own school
- **Prevention of last admin deletion**: Cannot delete the last super admin

## Role Hierarchy and Permissions

### Role Levels (Lowest to Highest)
1. **Teacher** - Can self-register, basic system access
2. **Supervisor** - Can observe and evaluate teachers
3. **School Head** - Can manage school users, create supervisors
4. **Division Admin** - Can manage schools, create school heads
5. **Super Admin** - Full system access, can create division admins

### Authorization Rules

#### Creating Users
- **Anyone with 'manage users' permission** can create teachers
- **School heads and above** can create supervisors
- **Division admins and super admins** can create school heads
- **Only super admins** can create division admins and super admins

#### School-Based Restrictions
- **School heads** can only create supervisors in their own school
- **All users** must be assigned to a valid school
- **Cross-school access** is prevented by default

## Best Practices for Role Management

### 1. Admin Panel Management
- **Use UserManagementController** for all administrative user creation
- **Never allow role selection** in public forms
- **Always validate school assignments** 
- **Use proper authorization middleware**

### 2. Invitation System (Recommended)
- **Implement email invitations** for higher roles
- **Require approval** from existing administrators
- **Set expiration dates** for invitation links
- **Log all invitation activities**

### 3. Audit Trail
- **Log all role changes** with user who made the change
- **Monitor failed registration attempts** with suspicious patterns
- **Regular security audits** of user roles and permissions
- **Alert on privilege escalation attempts**

### 4. Security Monitoring
- **Monitor for mass registration attempts**
- **Track role assignment changes**
- **Alert on unusual admin account creation**
- **Regular review of user permissions**

## Implementation Details

### Files Modified/Created

#### Controllers
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Updated with forced teacher role
- `app/Http/Controllers/Admin/UserManagementController.php` - New admin user management

#### Requests
- `app/Http/Requests/Auth/RegistrationRequest.php` - Secure registration validation

#### Middleware
- `app/Http/Middleware/CheckUserManagementPermission.php` - Authorization for user management

#### Views
- `resources/views/auth/register.blade.php` - Removed role selection, added school selection

#### Tests
- `tests/Feature/Auth/RegistrationTest.php` - Comprehensive security tests

### Database Schema Implications

#### Users Table
- `role` field is forced to 'teacher' during registration
- `school_id` is required and validated
- Email verification is required before access

#### Teachers Table
- Automatically created when teacher registers
- Links to user and school
- Stores department and years of service

## Security Checklist

### Registration Security
- [x] Role selection removed from public form
- [x] Role forced to 'teacher' in controller
- [x] School ID validation implemented
- [x] Suspicious pattern detection added
- [x] Comprehensive test coverage

### Authorization Security
- [x] Middleware for user management access
- [x] Role-based creation permissions
- [x] School-scoped restrictions
- [x] Prevention of privilege escalation

### Monitoring and Auditing
- [ ] Implement audit logging for role changes
- [ ] Add security event monitoring
- [ ] Create admin activity dashboard
- [ ] Set up security alerts

## Compliance Notes

This security policy ensures compliance with:
- **DepEd data protection requirements**
- **Role-based access control (RBAC) principles**
- **Least privilege principle**
- **Separation of duties**
- **Audit trail requirements**

## Future Enhancements

1. **Two-factor authentication** for admin accounts
2. **Session timeout policies** based on role
3. **IP whitelisting** for administrative access
4. **Automated security scanning** of user activities
5. **Regular security training** for administrators

---

**Last Updated**: May 2, 2026  
**Version**: 1.0  
**Next Review**: August 2, 2026
