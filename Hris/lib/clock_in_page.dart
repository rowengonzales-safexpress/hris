import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';

class ClockInPage extends StatefulWidget {
  final String type; // 'IN' or 'OUT'

  const ClockInPage({super.key, this.type = 'IN'});

  @override
  State<ClockInPage> createState() => _ClockInPageState();
}

class _ClockInPageState extends State<ClockInPage> with WidgetsBindingObserver {
  late Timer _timer;
  late DateTime _currentTime;
  String _currentAddress = "Finding location...";
  bool _isLoadingLocation = true;
  String _locationName =
      "Current Location"; // For the "Sr. San Jose Chapel" part
  StreamSubscription<ServiceStatus>? _serviceStatusStreamSubscription;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _currentTime = DateTime.now();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          _currentTime = DateTime.now();
        });
      }
    });

    // Initial check
    _checkLocationServiceAndPermission();

    // Listen for service status changes
    _serviceStatusStreamSubscription = Geolocator.getServiceStatusStream()
        .listen((ServiceStatus status) {
          if (status == ServiceStatus.disabled) {
            if (mounted) {
              setState(() {
                _currentAddress = "Location services are disabled.";
                _isLoadingLocation = false;
              });
              _showLocationServiceDisabledDialog();
            }
          } else {
            // Re-fetch location when enabled
            if (mounted) {
              setState(() {
                _isLoadingLocation = true;
                _currentAddress = "Finding location...";
              });
            }
            _getLocation();
          }
        });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _checkLocationServiceAndPermission();
    }
  }

  Future<void> _checkLocationServiceAndPermission() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (mounted) {
        setState(() {
          _currentAddress = "Location services are disabled.";
          _isLoadingLocation = false;
        });
        _showLocationServiceDisabledDialog();
      }
    } else {
      _getLocation();
    }
  }

  void _showLocationServiceDisabledDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text("GPS Disabled"),
          content: const Text("Please turn on your GPS to clock in/out."),
          actions: <Widget>[
            TextButton(
              child: const Text("Cancel"),
              onPressed: () {
                Navigator.of(context).pop();
              },
            ),
            TextButton(
              child: const Text("Open Settings"),
              onPressed: () {
                Geolocator.openLocationSettings();
                Navigator.of(context).pop();
              },
            ),
          ],
        );
      },
    );
  }

  Future<void> _getLocation() async {
    bool serviceEnabled;
    LocationPermission permission;

    // Test if location services are enabled.
    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      // Already handled by listener/check
      return;
    }

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        if (mounted) {
          setState(() {
            _currentAddress = "Location permissions are denied";
            _isLoadingLocation = false;
          });
        }
        return;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      if (mounted) {
        setState(() {
          _currentAddress =
              "Location permissions are permanently denied, we cannot request permissions.";
          _isLoadingLocation = false;
        });
      }
      return;
    }

    // When we reach here, permissions are granted and we can
    // continue accessing the position of the device.
    try {
      Position position = await Geolocator.getCurrentPosition();
      List<Placemark> placemarks = await placemarkFromCoordinates(
        position.latitude,
        position.longitude,
      );

      if (placemarks.isNotEmpty) {
        Placemark place = placemarks[0];
        // Construct address
        String address = [
          place.street,
          place.locality,
          place.administrativeArea,
          place.postalCode,
        ].where((element) => element != null && element.isNotEmpty).join(', ');

        String name = place.name ?? "Unknown Place";
        if (place.thoroughfare != null && place.thoroughfare!.isNotEmpty) {
          name = place.thoroughfare!;
        }

        if (mounted) {
          setState(() {
            _currentAddress = address;
            _locationName = name; // Or use a more specific name if available
            _isLoadingLocation = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _currentAddress = "No address found";
            _isLoadingLocation = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _currentAddress = "Error getting location: $e";
          _isLoadingLocation = false;
        });
      }
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _timer.cancel();
    _serviceStatusStreamSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final timeFormat = DateFormat('hh:mm:ss a');
    final dateFormat = DateFormat('EEEE, MMMM d');
    final isClockIn = widget.type == 'IN';
    final buttonText = isClockIn ? "Clock In" : "Clock Out";
    final themeColor = isClockIn ? const Color(0xFF1B4D8F) : Colors.orange;

    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          // Map Background (Placeholder)
          Positioned.fill(
            child: Container(
              color: const Color(0xFFC8F6D6), // Light green map color
              child: Stack(
                alignment: Alignment.center,
                children: [
                  // Map details simulation
                  Positioned(
                    top: 200,
                    left: 50,
                    child: Icon(
                      Icons.location_on,
                      color: Colors.blue.withOpacity(0.5),
                      size: 40,
                    ),
                  ),
                  Positioned(
                    top: 250,
                    right: 80,
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.1),
                            blurRadius: 4,
                          ),
                        ],
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.church,
                            size: 16,
                            color: Colors.grey,
                          ),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              _locationName,
                              style: const TextStyle(
                                fontSize: 12,
                                color: Colors.grey,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Center Marker with ripple effect simulation
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.blue.withOpacity(0.1),
                      border: Border.all(
                        color: Colors.blue.withOpacity(0.3),
                        width: 1,
                      ),
                    ),
                    child: Center(
                      child: Container(
                        width: 20,
                        height: 20,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white,
                          boxShadow: [
                            BoxShadow(color: Colors.black26, blurRadius: 4),
                          ],
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.navigation,
                            size: 14,
                            color: Colors.blue,
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Location Pill at bottom of map area
                  Positioned(
                    bottom: 180, // Adjust based on bottom sheet height
                    left: 20,
                    right: 20,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFF2E4C40), // Dark green pill
                        borderRadius: BorderRadius.circular(30),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.2),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.location_on,
                            color: Colors.white,
                            size: 20,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _isLoadingLocation
                                ? const Text(
                                    "Locating...",
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 14,
                                    ),
                                  )
                                : RichText(
                                    text: TextSpan(
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 14,
                                      ),
                                      children: [
                                        TextSpan(text: _currentAddress),
                                      ],
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                          ),
                          const SizedBox(width: 8),
                          // Google logo placeholder
                          const Text(
                            "Google",
                            style: TextStyle(
                              color: Colors.white70,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Back Button
          Positioned(
            top: 50,
            left: 16,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () => Navigator.of(context).pop(),
                  borderRadius: BorderRadius.circular(20),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    child: Row(
                      children: [
                        Icon(Icons.arrow_back, size: 20, color: Colors.black54),
                        SizedBox(width: 4),
                        Text(
                          "Back",
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

          // Bottom Sheet
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.only(
                top: 24,
                bottom: 32,
                left: 24,
                right: 24,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black12,
                    blurRadius: 10,
                    offset: Offset(0, -2),
                  ),
                ],
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Time
                  Text(
                    timeFormat.format(_currentTime),
                    style: const TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1B3B2F), // Dark green text
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Date
                  Text(
                    "Today is ${dateFormat.format(_currentTime)}",
                    style: TextStyle(fontSize: 16, color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 32),
                  // Slide Button
                  Container(
                    height: 56,
                    decoration: BoxDecoration(
                      border: Border.all(
                        color: themeColor,
                      ), // Blue or Orange border
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: Center(
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.arrow_forward,
                                  color: themeColor,
                                  size: 20,
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  buttonText,
                                  style: TextStyle(
                                    color: themeColor,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        Container(
                          width: 56,
                          height: 56,
                          decoration: BoxDecoration(
                            border: Border(left: BorderSide(color: themeColor)),
                          ),
                          child: Icon(
                            Icons.keyboard_arrow_up,
                            color: themeColor,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
