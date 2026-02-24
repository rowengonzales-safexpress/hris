import 'package:flutter/material.dart';

class LeaveLoadingPage extends StatefulWidget {
  const LeaveLoadingPage({super.key});

  @override
  State<LeaveLoadingPage> createState() => _LeaveLoadingPageState();
}

class _LeaveLoadingPageState extends State<LeaveLoadingPage> {
  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.of(context).pushReplacementNamed('/leave');
      }
    });
  }

  Widget _skeletonCard() {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
      height: 100,
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: BorderRadius.circular(16),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Leave'),
          centerTitle: true,
          elevation: 0.5,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back),
            onPressed: () {
              Navigator.of(context).pop();
            },
          ),
          bottom: const TabBar(
            indicatorColor: Colors.green,
            labelColor: Colors.green,
            unselectedLabelColor: Colors.grey,
            tabs: [
              Tab(text: 'Pending'),
              Tab(text: 'Approved'),
              Tab(text: 'Rejected'),
            ],
          ),
        ),
        body: SafeArea(
          child: TabBarView(
            children: [
              ListView(
                padding: const EdgeInsets.symmetric(vertical: 16),
                children: [
                  _skeletonCard(),
                  _skeletonCard(),
                  _skeletonCard(),
                ],
              ),
              ListView(
                padding: const EdgeInsets.symmetric(vertical: 16),
                children: [
                  _skeletonCard(),
                  _skeletonCard(),
                  _skeletonCard(),
                ],
              ),
              ListView(
                padding: const EdgeInsets.symmetric(vertical: 16),
                children: [
                  _skeletonCard(),
                  _skeletonCard(),
                  _skeletonCard(),
                ],
              ),
            ],
          ),
        ),
        floatingActionButton: FloatingActionButton(
          onPressed: () {},
          backgroundColor: Colors.green,
          child: const Icon(Icons.add),
        ),
      ),
    );
  }
}

