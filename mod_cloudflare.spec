Name:		mod_cloudflare
Version:	1.0.2
Release:	1%{?dist}
Summary:	Cloudflare Apache Module

Group:		System Environment/Daemons
License:	ASL-2.0
URL:		http://www.cloudflare.com/
Source0:	https://raw.github.com/cloudflare/CloudFlare-Tools/master/mod_cloudflare.c
BuildRoot:	%(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

BuildRequires:	httpd-devel
Requires:	httpd

%description
CloudFlare acts as a proxy, which means that your visitors are routed through
the CloudFlare network and you do not see their original IP address. This
module uses HTTP headers provided by the CloudFlare proxy to log the real IP
address of the visitor.

%prep
%setup -c -T
cp $RPM_SOURCE_DIR/mod_cloudflare.c .

%build
apxs -c mod_cloudflare.c

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/%{_libdir}/httpd/modules/
mkdir -p $RPM_BUILD_ROOT/etc/httpd/conf.d/

install -m 755 .libs/mod_cloudflare.so $RPM_BUILD_ROOT/%{_libdir}/httpd/modules/mod_cloudflare.so
echo "LoadModule cloudflare_module modules/mod_cloudflare.so" > $RPM_BUILD_ROOT/etc/httpd/conf.d/cloudflare.conf
chmod 644 $RPM_BUILD_ROOT/etc/httpd/conf.d/cloudflare.conf


%clean
rm -rf $RPM_BUILD_ROOT


%files
%defattr(-,root,root,-)
%{_libdir}/httpd/modules/mod_cloudflare.so
/etc/httpd/conf.d/cloudflare.conf

%changelog
* Wed Jan 18 2012 Corey Henderson <corman@cormander.com> [1.0.2-1.el6]
- Initial build.

