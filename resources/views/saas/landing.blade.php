<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Studio Management System') }} | Modern Studio Management Platform</title>
    <meta name="description" content="A modern studio management platform for institutes, academies, fitness studios, dance schools and learning centres to