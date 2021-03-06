
# step1.download
This step downloads job postings from various web projects and temporary saves the downloaded into separate files

## The story begins in the `main.sh` shell script run by Linux's cron ##
- `main.sh` looks at `projects-on` section of `settings.json` file, extracts every value assigned to `entrance_sh_download`.
- Every found and existing `entrance.sh` is being run. One entry after another. The order of run is the same as coded in the `settings.json`. From top to bottom.
- `entrance.sh` code usually triggers `index.php` file (if the particular project is running on php, - this is by default). And then things go as the PHP code inside `index.php` wants...
- At the moment `entrance.sh` is made to work with PHP code only. However, you are not limited as you may always write your own `entrance.sh` file and trigger whatever you want in there: Python? Ruby? scripting shell only? You name it.

## What does it mean to add the new project (job board, career website, etc.)? ##
- Put your code under `download/{your_language}/project/{project_domain}/{project_tld_or_alternative}`
- Your code should have `entrance.sh` file, - this file is the only mandatory file as it is the only file that is run by the system. Write your code in this file and/or create additional files and/or directories inside of the same directory.
- Take a look at `settings.json` file, - this file holds the configuration and you have to listen for what configuration says. The point of `settings.json` file is to change the behaviour of your code if needed. The file isn't big, but if you felt into troubles understanding that - drop me a message, I will help you.
- That's it. The rest is optional.

## Could it be easier? ##
- Of course! For example, create your project by using PHP. It is easier than coding shell scripts, coding everything from scratch and so on...

## Add your own job board website or career page by using PHP ##
- Use the code of already existing similar project as the template. Just simply, do copy > paste.
- Change namespaces and so rename pasted directories to your own names - to correspond this structure: `download/{your_language}/project/{project_domain}/{project_tld_or_alternative}`
- After you will look at the content you pasted you will see that there are 5 different files:
-- `Classes/Auditor.php` - the file is not used really, because all methods come from parent of this class. Anyway, leave this file inside of your project for the brighter future. Pay attention to namespaces - you still need to modify them to match your structure.
-- `Classes/Browser.php` - the file usually has two methods where one is for the next page url, the other - for job urls of a particular page of the entire list to get.
-- `Classes/Job.php` - the file with methods named as information required to be extracted out of the job ad. For example, the method `content_static` - used for static content (job ad itself) to extract, `content_dynamic` - optional file for dynamic content to extract (for example: applicants number, page views statistic, etc.). The only mandatory method is `content_static`. The rest goes by default if you do not specify. The default is ok.
-- `index.php` - the main file that runs all php logic. You usually do not need to modify this.
-- `entrance.sh` - the main file that triggers `index.php` file. You usually do not need to modify this.
- Need any extra PHP libraries? No problems! Composer is being used in the project.

## Want your favourite job board or career page to be added? ##
- Do it by yourself (it should be easy if you are a developer)
- Or call me to do that for you!

## Projects added: ##
- cvbankas.lt

## Projects to be added: ##
- 01.net
- 6 Figure Jobs
- Accounting.com
- AccountWorld.com
- Albertajobs.com
- allstarjobs.ca
- America's Job Bank
- American Economic Association
- American Finance Association
- American Securitization Forum
- Association for Financial Professionals
- Association of Business Economists
- Association of Corporate Counsel
- Association of Investment Management
- Association of Real Estate Women
- BCTechnology.com
- bestjobs4grads.com
- Bing
- Black Data Processing Associates
- Bloomberg
- Brokerhunter.com
- ca.jobdiagnosis.com
- Cadremploi
- Cadresonline
- CallCareers.com
- CallCentreJob.ca
- CanadaIT.com
- CanJobs.com
- Capital Markets Credit Analysts
- Career Builder
- CareerEdge
- Careerjournal.com
- CareerTimes
- Carrefour jeunesse-emploi Rimouski-
- CFAI
- Challenger, Gray, & Christmas
- CityJobs
- City of Toronto (http://www.toronto.ca/employment/)
- CJOL
- Community Centre Job Bank
- ConnectMoms
- Corriere Dellasera
- Craigslist
- creditjob.com
- CREW.org
- Cultural Careers Council Ontario (workinculture.ca)
- DBM Jobscout
- Developpeur.ca
- Dice
- Econ Jobs
- eFinancial Careers
- Eluta.ca
- Eluta.ca
- Emploi informatique
- Emploi Québec
- Experisjobs.us
- Facebook
- FAZ
- FIASI
- Financial Times
- Financial times Germany
- FINS.com
- Frankfurter Allgemeine Zeitun
- GAAPJobs.com
- Global Association Risk Professionals
- GoodWork Canada (http://www.goodworkcanada.ca/)
- Google
- Government of Canada - Job Bank
- HigherBracket.ca
- HireCanadianMilitary.com
- hired.ca
- Hire Ground
- HotJobs (HotJobs.com)
- Indeed
- InfoPresseJob
- ISARTA
- Japan Times
- Japan Times Job
- Jeff Gaulin (jeffgaulin.com)
- jobbank.gc.ca
- Jobboom
- Job Finance
- Jobillico
- Jobpilot.de
- Jobrapido
- jobscentral.com.sg
- jobscout24.de
- Jobsdb
- Jobserve
- JobShark
- JobShop.ca
- jobstreet.com
- Journal of Finance
- Keljob
- Kijiji
- Ladder.com
- La Stampa
- Latinos in Information Science
- Lee Hecht Harrison
- Le Monde
- LesPACS
- LinkedIn
- magny.org
- Masthead (http://www.mastheadonline.com/jobs/)
- Maths-fi
- maxhire.net
- MBA Focus
- Media Job Search (http://www.mediajobsearchcanada.com/)
- Meetingjobs.com
- Monster (monster.ca, etc.)
- MonsterTrak
- Moody's Alumni Network
- Moodys.com
- Moodys Alumni Website
- Moscow Times
- Nacelink
- Neuvoo
- New York Society of Security
- New York Times
- NiceJobs
- Nikkei Net
- Nikkei News
- NY State Bar Association
- OfficeJobs.com
- One Wire
- Peter's New Jobs (PNJ)
- Proactive Approach
- Quan Finance.com
- REALpac.ca
- Recu-Nabi
- Right Management
- S1jobs
- SalesJobsCanada.com
- San Francisco Chronicle
- seek.com.au
- SelectLeader.com
- Selectleaders
- Service Canada Job Bank (www.jobbank.gc.ca)
- silkroad.com
- Simply Hired
- Society for Human Resources Managers
- South China Morning Post (SCMP)
- Star Newspaper
- stellenanzeigen.de
- stepstone.de
- Straits Times
- T-Net
- Tactics for Success
- TalentOyster
- TaxTalent
- The Australia Financial Review
- The Australian
- The Ayers Group
- The Guardian
- TheLadders
- The Muse (https://www.themuse.com/jobs)
- The Professional Risk Manager
- The Sydney Morning Herald
- TorontoJobCentre.com
- TorontoJobs.ca
- Twitter
- Vault.com
- Vedemosti
- Wall Street Journal
- Wallstreetoasis.com
- WeHire.ca
- Women in Information Technology
- Working.com
- WorkingCalgaryJobs.com
- workopolis.com
- WorldatWork
- www.51job.com
- www.chinahr.com
- www.cjol.com
- www.headhunter.ru
- www.newsmth.net
- www.waterlootechjobs.com
- www.yingjiesheng.com
- XING
- Ying Jiesheng
- Zhaopin.com
- ...and more

## The list is missing something important? ##
Let me know.