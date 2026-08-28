import { chromium } from 'playwright';

const requiredEnv = (name) => {
    const value = process.env[name];
    if (!value) throw new Error(`Missing required secret: ${name}`);
    return value;
};

const appUrl = new URL(requiredEnv('APP_URL'));
if (appUrl.protocol !== 'https:') {
    throw new Error('APP_URL must use HTTPS');
}

const loadDaily = async () => {
    const browser = await chromium.launch({ headless: true });
    try {
        const page = await browser.newPage();
        await page.goto(appUrl.toString(), { waitUntil: 'networkidle', timeout: 60000 });

        const response = await page.evaluate(async ({ team, password }) => {
            const apiResponse = await fetch('/api/daily.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ team, password }),
                credentials: 'same-origin'
            });

            return {
                status: apiResponse.status,
                contentType: apiResponse.headers.get('content-type') || '',
                body: await apiResponse.text()
            };
        }, {
            team: requiredEnv('DAILY_TEAM'),
            password: requiredEnv('DAILY_PASSWORD')
        });

        if (response.status !== 200 || !response.contentType.includes('application/json')) {
            throw new Error(`Daily API returned an unexpected response (${response.status})`);
        }

        return JSON.parse(response.body);
    } finally {
        await browser.close();
    }
};

const notifyTeams = async (daily) => {
    const message = [
        '📋 Daily em 5 min',
        '',
        `Apresentador: **${daily.presenter.name}**`,
        'Prepare updates, impedimentos e próximos passos.',
        '',
        `Fonte: ${appUrl.toString()}dashboard.php`
    ].join('\n');

    const response = await fetch(requiredEnv('TEAMS_FLOW_WEBHOOK'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            team: daily.team,
            today: daily.today,
            presenter: daily.presenter,
            message
        })
    });

    if (!response.ok) {
        throw new Error(`Teams Flow returned HTTP ${response.status}`);
    }
};

const daily = await loadDaily();

if (!daily.is_workday || daily.is_holiday || !daily.presenter) {
    console.log('No eligible presenter. Notification skipped.');
    process.exit(0);
}

await notifyTeams(daily);
console.log('Daily notification sent.');
