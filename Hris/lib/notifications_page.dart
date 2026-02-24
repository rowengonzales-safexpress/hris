import 'package:flutter/material.dart';
import 'navigations_detail_page.dart';

class NotificationsPage extends StatelessWidget {
  const NotificationsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Notifications'),
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
              Tab(text: 'Activities'),
              Tab(text: 'Promotions'),
            ],
          ),
        ),
        body: const SafeArea(
          child: TabBarView(
            children: [
              _ActivitiesTab(),
              _PromotionsTab(),
            ],
          ),
        ),
      ),
    );
  }
}

class _ActivitiesTab extends StatelessWidget {
  const _ActivitiesTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          'THIS MONTH',
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey.shade700,
            fontWeight: FontWeight.w600,
            letterSpacing: 1,
          ),
        ),
        const SizedBox(height: 8),
        _NotificationCard(
          title: 'COA Application Approved!',
          subtitle: 'Your request that was filed on February 09, 2026',
          timeAgo: '1 week ago',
          highlighted: true,
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => const NavigationsDetailPage(),
              ),
            );
          },
        ),
        const _NotificationCard(
          title: 'COA Application Approved!',
          subtitle: 'Your request that was filed on February 14, 2026',
          timeAgo: '1 week ago',
          highlighted: true,
        ),
        const SizedBox(height: 16),
        Text(
          'JANUARY 2026',
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey.shade700,
            fontWeight: FontWeight.w600,
            letterSpacing: 1,
          ),
        ),
        const SizedBox(height: 8),
        const _NotificationCard(
          title: 'COA Application Approved!',
          subtitle: 'Your request that was filed on January 29, 2026',
          timeAgo: '3 weeks ago',
        ),
        const _NotificationCard(
          title: 'Leave Application Approved!',
          subtitle: 'Your request that was filed on January 31, 2026',
          timeAgo: '3 weeks ago',
        ),
        const _NotificationCard(
          title: 'Leave Application Approved!',
          subtitle: 'Your request that was filed on January 26, 2026',
          timeAgo: '3 weeks ago',
        ),
      ],
    );
  }
}

class _PromotionsTab extends StatelessWidget {
  const _PromotionsTab();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Text(
        'No promotions yet',
        style: TextStyle(
          fontSize: 14,
          color: Colors.grey.shade600,
        ),
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final String timeAgo;
  final bool highlighted;
  final VoidCallback? onTap;

  const _NotificationCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.timeAgo,
    this.highlighted = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 4),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: highlighted ? Colors.lightBlue.shade50 : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: highlighted ? Colors.transparent : Colors.grey.shade300,
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              height: 40,
              width: 40,
              decoration: BoxDecoration(
                color: Colors.green.shade50,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.badge_outlined,
                color: Colors.green,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.grey,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    timeAgo,
                    style: const TextStyle(
                      fontSize: 11,
                      color: Colors.grey,
                    ),
                  ),
                ],
              ),
            ),
            IconButton(
              onPressed: () {},
              icon: const Icon(
                Icons.more_vert,
                size: 18,
                color: Colors.grey,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
