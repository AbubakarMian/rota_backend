🏥 AI-Driven Doctor Rota Scheduling System
Overview

This project implements an AI-based rota scheduling system for hospitals operating under doctor shortages and conflicting operational constraints. The system was designed for a Saudi military hospital where manual scheduling was infeasible due to scale, fairness, and compliance requirements.

The solution uses genetic algorithms and constraint optimization to generate feasible and near-optimal schedules while maintaining explainability and operational control.

Problem Statement

Hospitals require:

Guaranteed doctor coverage per shift

Fair duty distribution

Compliance with rest and leave policies
While facing:

Chronic staff shortages

Conflicting leave and duty requests

Variable role requirements per shift

Traditional rule-based scheduling fails under these conditions.

Key Features

Multi-shift scheduling (Morning / Evening / Night)

Role-based allocation:

Duty Doctor

Consultant

UCC

Registrar

Resident

Leave management:

Annual

Casual

Medical

Mandatory rest gaps between consecutive shifts

Minimum & maximum duty limits per doctor

Special duty and special off requests

AI & Optimization Approach

Modeled as a constraint optimization problem

Used genetic algorithms to search solution space

Fitness function balances:

Coverage

Fairness

Policy compliance

Penalty-based scoring instead of hard rejection

Iterative regeneration when no perfect solution exists

Constraint Relaxation Strategy

When no valid solution is found, rules are relaxed in the following order:

General Duties Requests

Special Duties Requests

Special Off Requests

Resident & Registrar pairing

Regular Leaves

Consecutive Shift Restrictions

This guarantees operational continuity even under extreme constraints.

Why Genetic Algorithms?

Handles large combinatorial search spaces

Adapts well to conflicting constraints

Produces explainable, adjustable solutions

Suitable for real-world healthcare operations

Outcome

Enabled continuous hospital operations

Reduced scheduling conflicts and manual effort

Delivered a transparent AI system suitable for healthcare governance

Technologies

Genetic Algorithms

Constraint Optimization

PHP / Python (implementation-agnostic)

Relational Databases

Linux Server Environment
