#include "UrlDownloader.h"
#include "Outputer.h"

UrlDownloader::UrlDownloader()
{
    o = new Outputer();
}

UrlDownloader::~UrlDownloader()
{
}

void UrlDownloader::Download(QUrl u)
{
    int key = GetOrCreateMgr();
    urls[key] = u;
    QNetworkRequest request = QNetworkRequest(u);
    request.setRawHeader("User-Agent", "User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.106 Safari/535.2");
    mgrs[key]->get(request);
    o->Set(mgrs.size());
}

void UrlDownloader::replyFinished(QNetworkReply * rep)
{
    int key = GetKeyByUrl(rep->url());
    if (!key)
        return;

    QUrl oldOne = rep->url();
    urls[key] = QUrl();
    rep->deleteLater();
    Download(oldOne);
}

int UrlDownloader::GetOrCreateMgr()
{
    for(TheMapMgr::Iterator itr = mgrs.begin(); itr != mgrs.end(); ++itr)
        if (urls.value(itr.key()) == QUrl())
            return itr.key();

    QNetworkAccessManager* mgr = new QNetworkAccessManager(this);
    connect(mgr, SIGNAL(finished(QNetworkReply*)), this, SLOT(replyFinished(QNetworkReply*)));
    int theKey;
    if (!mgrs.empty())
    {
        TheMapMgr::Iterator itr = mgrs.end();
        --itr;
        theKey = itr.key()+1;
    }
    else
        theKey = 0;

    mgrs.insert(theKey, mgr);
    return theKey;
}

int UrlDownloader::GetKeyByUrl(QUrl u)
{
    for(TheMapUrl::Iterator itr = urls.begin(); itr != urls.end(); ++itr)
        if (itr.value() == u)
            return itr.key();

    return 0;
}
