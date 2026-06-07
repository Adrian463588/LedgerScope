---
name: mobile-development
description: "Build mobile apps with React Native, Flutter, or native iOS/Android. Use for cross-platform development, native modules, navigation, state management, app store deployment."
argument-hint: "[platform or feature]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Mobile Development

Cross-platform and native mobile development across React Native, Flutter, iOS (Swift), and Android (Kotlin).

## When to Use

- Building cross-platform mobile apps
- Native module integration
- Navigation and deep linking setup
- Mobile state management
- Push notifications
- App store build and deployment
- Platform-specific UI/UX adaptation

## Platform Selection

| Platform | Best For |
|----------|----------|
| **React Native** | JavaScript teams, web code sharing, rapid iteration |
| **Flutter** | Pixel-perfect custom UI, single codebase, performance |
| **Swift (iOS)** | Native iOS, complex hardware integration, App Store optimization |
| **Kotlin (Android)** | Native Android, Google Play optimization |

## React Native Checklist

- [ ] Use Expo (managed workflow) unless native modules required
- [ ] Navigation: React Navigation v7 (stack + tab + drawer)
- [ ] State: Zustand or TanStack Query for server state
- [ ] Styling: StyleSheet API or NativeWind (Tailwind for RN)
- [ ] Lists: FlatList/FlashList (never ScrollView for long lists)
- [ ] Images: Expo Image or FastImage for caching
- [ ] Testing: Jest + React Native Testing Library

## Flutter Checklist

- [ ] State management: Riverpod (preferred) or BLoC
- [ ] Navigation: GoRouter for declarative routing
- [ ] HTTP: Dio with interceptors
- [ ] Local storage: Hive or SharedPreferences
- [ ] Testing: widget tests + integration tests

## Performance Rules

- Avoid inline function definitions in render (causes re-renders)
- Use `memo`/`useCallback` for list item components
- Lazy-load heavy screens
- Compress images before bundling
- Profile with Flipper (RN) or Flutter DevTools

## Platform-Specific UI

- Respect platform conventions (iOS uses bottom sheets, Android uses bottom nav)
- Test on both iOS and Android (different font rendering, safe areas)
- Handle notch/Dynamic Island/punch-hole camera with SafeAreaView

## Build & Deployment

```bash
# React Native / Expo
eas build --platform ios
eas build --platform android
eas submit --platform ios

# Flutter
flutter build ios --release
flutter build appbundle --release
```

## References

- `references/react-native.md` — RN patterns and Expo setup
- `references/flutter.md` — Flutter architecture and widgets
- `references/navigation.md` — Deep linking and routing
- `references/push-notifications.md` — FCM and APNs setup
- `references/app-store.md` — Submission and review guidelines
