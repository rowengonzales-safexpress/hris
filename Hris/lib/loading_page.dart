import 'package:flutter/material.dart';

class LoadingPage extends StatefulWidget {
  const LoadingPage({super.key});

  @override
  State<LoadingPage> createState() => _LoadingPageState();
}

class _LoadingPageState extends State<LoadingPage> {
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.of(context).pushReplacementNamed('/home');
      }
    });
  }

  Widget _skeletonBox({
    double height = 20,
    double width = double.infinity,
    EdgeInsetsGeometry margin = EdgeInsets.zero,
    BorderRadiusGeometry borderRadius =
        const BorderRadius.all(Radius.circular(12)),
  }) {
    return Container(
      height: height,
      width: width,
      margin: margin,
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: borderRadius,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              _skeletonBox(
                height: 40,
                width: 180,
                margin: const EdgeInsets.only(bottom: 16),
              ),
              _skeletonBox(
                height: 80,
                margin: const EdgeInsets.only(bottom: 16),
              ),
              _skeletonBox(
                height: 80,
                margin: const EdgeInsets.only(bottom: 24),
              ),
              Expanded(
                child: GridView.builder(
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: 8,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 4,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 1,
                  ),
                  itemBuilder: (_, __) {
                    return _skeletonBox();
                  },
                ),
              ),
              _skeletonBox(
                height: 40,
                margin: const EdgeInsets.only(bottom: 16),
              ),
              Row(
                children: [
                  Expanded(
                    child: _skeletonBox(
                      height: 80,
                      margin: const EdgeInsets.only(right: 8),
                    ),
                  ),
                  Expanded(
                    child: _skeletonBox(
                      height: 80,
                      margin: const EdgeInsets.only(left: 8),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

