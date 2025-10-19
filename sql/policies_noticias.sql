-- ============================================================================
-- POLICIES RLS PARA NOTICIAS Y CATEGORÍAS
-- ============================================================================

-- CATEGORÍAS: permitir lectura y gestión completa
CREATE POLICY IF NOT EXISTS anon_select_categorias ON public.categorias 
  FOR SELECT TO anon 
  USING (true);

CREATE POLICY IF NOT EXISTS anon_insert_categorias ON public.categorias 
  FOR INSERT TO anon 
  WITH CHECK (true);

CREATE POLICY IF NOT EXISTS anon_update_categorias ON public.categorias 
  FOR UPDATE TO anon 
  USING (true);

CREATE POLICY IF NOT EXISTS anon_delete_categorias ON public.categorias 
  FOR DELETE TO anon 
  USING (true);

-- NOTICIAS: permitir lectura y gestión completa
CREATE POLICY IF NOT EXISTS anon_select_noticias ON public.noticias 
  FOR SELECT TO anon 
  USING (true);

CREATE POLICY IF NOT EXISTS anon_insert_noticias ON public.noticias 
  FOR INSERT TO anon 
  WITH CHECK (true);

CREATE POLICY IF NOT EXISTS anon_update_noticias ON public.noticias 
  FOR UPDATE TO anon 
  USING (true);

CREATE POLICY IF NOT EXISTS anon_delete_noticias ON public.noticias 
  FOR DELETE TO anon 
  USING (true);

-- Verificar políticas creadas
SELECT 
    schemaname,
    tablename,
    policyname,
    permissive,
    roles,
    cmd
FROM pg_policies
WHERE tablename IN ('noticias', 'categorias')
ORDER BY tablename, policyname;
