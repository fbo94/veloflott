<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain;

use Auth\Domain\Role;

describe('Role', function () {
    describe('label', function () {
        it('returns correct label for super_admin', function () {
            expect(Role::SUPER_ADMIN->label())->toBe('Super Administrateur');
        });

        it('returns correct label for admin', function () {
            expect(Role::ADMIN->label())->toBe('Administrateur');
        });

        it('returns correct label for manager', function () {
            expect(Role::MANAGER->label())->toBe('Manager');
        });

        it('returns correct label for employee', function () {
            expect(Role::EMPLOYEE->label())->toBe('Employé');
        });
    });

    describe('isHigherThan', function () {
        it('super_admin is higher than all other roles', function () {
            expect(Role::SUPER_ADMIN->isHigherThan(Role::ADMIN))->toBeTrue();
            expect(Role::SUPER_ADMIN->isHigherThan(Role::MANAGER))->toBeTrue();
            expect(Role::SUPER_ADMIN->isHigherThan(Role::EMPLOYEE))->toBeTrue();
        });

        it('admin is higher than manager and employee', function () {
            expect(Role::ADMIN->isHigherThan(Role::MANAGER))->toBeTrue();
            expect(Role::ADMIN->isHigherThan(Role::EMPLOYEE))->toBeTrue();
            expect(Role::ADMIN->isHigherThan(Role::SUPER_ADMIN))->toBeFalse();
        });

        it('manager is higher than employee only', function () {
            expect(Role::MANAGER->isHigherThan(Role::EMPLOYEE))->toBeTrue();
            expect(Role::MANAGER->isHigherThan(Role::ADMIN))->toBeFalse();
            expect(Role::MANAGER->isHigherThan(Role::SUPER_ADMIN))->toBeFalse();
        });

        it('employee is not higher than any role', function () {
            expect(Role::EMPLOYEE->isHigherThan(Role::MANAGER))->toBeFalse();
            expect(Role::EMPLOYEE->isHigherThan(Role::ADMIN))->toBeFalse();
            expect(Role::EMPLOYEE->isHigherThan(Role::SUPER_ADMIN))->toBeFalse();
        });

        it('same role is not higher than itself', function () {
            expect(Role::ADMIN->isHigherThan(Role::ADMIN))->toBeFalse();
            expect(Role::MANAGER->isHigherThan(Role::MANAGER))->toBeFalse();
        });
    });

    describe('all', function () {
        it('returns all roles with value and label', function () {
            $roles = Role::all();

            expect($roles)->toBeArray();
            expect($roles)->toHaveCount(4);

            $values = array_column($roles, 'value');
            expect($values)->toContain('super_admin');
            expect($values)->toContain('admin');
            expect($values)->toContain('manager');
            expect($values)->toContain('employee');
        });

        it('each role has value and label keys', function () {
            $roles = Role::all();

            foreach ($roles as $role) {
                expect($role)->toHaveKeys(['value', 'label']);
                expect($role['value'])->toBeString();
                expect($role['label'])->toBeString();
            }
        });
    });

    describe('cases', function () {
        it('returns all enum cases', function () {
            $cases = Role::cases();

            expect($cases)->toHaveCount(4);
            expect($cases)->toContain(Role::SUPER_ADMIN);
            expect($cases)->toContain(Role::ADMIN);
            expect($cases)->toContain(Role::MANAGER);
            expect($cases)->toContain(Role::EMPLOYEE);
        });
    });

    describe('from', function () {
        it('creates role from valid string', function () {
            expect(Role::from('super_admin'))->toBe(Role::SUPER_ADMIN);
            expect(Role::from('admin'))->toBe(Role::ADMIN);
            expect(Role::from('manager'))->toBe(Role::MANAGER);
            expect(Role::from('employee'))->toBe(Role::EMPLOYEE);
        });

        it('throws exception for invalid string', function () {
            expect(fn () => Role::from('invalid'))->toThrow(\ValueError::class);
        });
    });

    describe('tryFrom', function () {
        it('returns role for valid string', function () {
            expect(Role::tryFrom('admin'))->toBe(Role::ADMIN);
        });

        it('returns null for invalid string', function () {
            expect(Role::tryFrom('invalid'))->toBeNull();
        });
    });
});
