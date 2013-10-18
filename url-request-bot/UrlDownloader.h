#ifndef URLDOWNLOADER_H
#define URLDOWNLOADER_H

#include <QByteArray>
#include <QNetworkAccessManager>
#include <QNetworkReply>
#include <QNetworkRequest>
#include <QString>
#include <QList>
#include "Outputer.h"

typedef QMap<int, QNetworkAccessManager*> TheMapMgr;
typedef QMap<int, QUrl> TheMapUrl;
class UrlDownloader : public QObject
{
    Q_OBJECT

    public:
        UrlDownloader();
        ~UrlDownloader();

        void Download(QUrl u);
        void Download(QString s) { Download(QUrl(s)); }
        int GetOrCreateMgr();
        int GetKeyByUrl(QUrl u);

    private slots:
        void replyFinished(QNetworkReply*);

    private:
        Outputer* o;
        TheMapMgr mgrs;
        TheMapUrl urls;
};

#endif // URLDOWNLOADER_H
